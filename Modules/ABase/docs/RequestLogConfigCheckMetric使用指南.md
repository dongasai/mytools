# RequestLogConfigCheckMetric 使用指南

**创建时间**: 2026-08-24
**模块**: ABase

---

## 一、Metric 介绍

`RequestLogConfigCheckMetric` 是一个 Dcat Admin Metric 卡片，用于验证请求日志配置的正确性。

### 验证内容

当 `REQUEST_LOG_ENABLED=true` 时，会检查：

1. ✅ **数据库连接配置**: 检查 `dblog` 连接是否配置
2. ✅ **独立数据库**: 验证 `dblog` 是否与主数据库不同（建议独立）
3. ✅ **连接可用性**: 测试 `dblog` 连接是否可用
4. ✅ **数据表存在**: 检查 `sys_request_logs` 表是否存在
5. ✅ **数据记录**: 检查表是否有数据（可选）
6. ✅ **环境变量**: 验证 `REQUEST_LOG_MAX_RECORDS` 配置是否合理

---

## 二、使用方式

### 方式一：在 Dashboard 中使用

编辑 `Modules/DcatAdmin/DcatAdmin/Controllers/DashboardController.php`：

```php
<?php

namespace Modules\DcatAdmin\DcatAdmin\Controllers;

use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;
use Modules\ABase\DcatAdmin\Metrics\RequestLogConfigCheckMetric;

class DashboardController
{
    public function index(Content $content)
    {
        return $content
            ->header('Dashboard')
            ->description('系统概览')
            ->body(function (Row $row) {
                // 添加请求日志配置检查卡片
                $row->column(6, new RequestLogConfigCheckMetric());

                // 其他卡片...
            });
    }
}
```

### 方式二：在自定义页面中使用

```php
use Modules\ABase\DcatAdmin\Metrics\RequestLogConfigCheckMetric;

public function index(Content $content)
{
    return $content
        ->body(function (Row $row) {
            $row->column(12, new RequestLogConfigCheckMetric());
        });
}
```

### 方式三：在 Grid 中使用

```php
use Modules\ABase\DcatAdmin\Metrics\RequestLogConfigCheckMetric;

protected function grid()
{
    return Grid::make(new YourModel, function (Grid $grid) {
        // 在 Grid 顶部显示 Metric
        $grid->header(function () {
            return new RequestLogConfigCheckMetric();
        });

        // Grid 配置...
    });
}
```

---

## 三、显示效果

### 未启用状态

```
┌─────────────────────────────────────┐
│     请求日志配置检查                 │
│                                     │
│         ℹ️                          │
│   请求日志功能未启用                 │
│                                     │
│   如需启用，请在 .env 中配置：        │
│   REQUEST_LOG_ENABLED=true          │
│                                     │
└─────────────────────────────────────┘
```

### 配置正常状态

```
┌─────────────────────────────────────┐
│     请求日志配置检查                 │
│                                     │
│         ✓                          │
│   请求日志配置正常                   │
│                                     │
│   当前条数: 72 条                   │
│   数据库连接: dblog                 │
│   数据表: sys_request_logs          │
│   保留记录: 100000 条               │
│                                     │
└─────────────────────────────────────┘
```

### 发现问题状态

```
┌─────────────────────────────────────┐
│     请求日志配置检查                 │
│                                     │
│   ⚠ 请求日志配置存在问题 [2 个问题] │
│                                     │
│   • 未配置 dblog 数据库连接         │
│   • sys_request_logs 表不存在       │
│                                     │
│   当前条数: 0 条                    │
│   数据库连接: dblog                 │
│   数据表: sys_request_logs          │
│   保留记录: 100000 条               │
│                                     │
└─────────────────────────────────────┘
```

---

## 四、验证逻辑详解

### 1. 启用检查

```php
$enabled = config('abase.request_log.enabled', false);

if (!$enabled) {
    // 未启用，返回提示信息
    return [];
}
```

### 2. 数据库连接检查

```php
// 检查 dblog 连接是否配置
$dblogConfig = config('database.connections.dblog');

if (!$dblogConfig) {
    $issues[] = '未配置 dblog 数据库连接';
}

// 检查是否独立数据库
if ($dblogConfig['database'] === $defaultConfig['database']) {
    $issues[] = '建议使用独立数据库';
}
```

### 3. 连接测试

```php
try {
    DB::connection('dblog')->getPdo();
} catch (\Exception $e) {
    $issues[] = 'dblog 数据库连接失败: ' . $e->getMessage();
}
```

### 4. 表存在性检查

```php
if (!Schema::connection('dblog')->hasTable('sys_request_logs')) {
    $issues[] = 'sys_request_logs 表不存在';
}
```

---

## 五、直接调用服务

如果需要在其他地方使用检查服务：

```php
use Modules\ABase\Services\RequestLogConfigCheckService;

// 获取问题列表
$issues = RequestLogConfigCheckService::checkConfigs();

// 获取问题数量
$count = RequestLogConfigCheckService::getIssueCount();

// 获取摘要信息
$summary = RequestLogConfigCheckService::getSummary();

// 获取配置信息
$config = RequestLogConfigCheckService::getConfigInfo();

// 获取当前日志条数
$currentCount = RequestLogConfigCheckService::getCurrentLogCount();
```

**返回示例**:

```php
// getSummary() 返回
[
    'enabled' => true,
    'total' => 2,
    'issues' => [
        '未配置 dblog 数据库连接',
        'sys_request_logs 表不存在',
    ],
    'status' => 'warning',
    'message' => '请求日志配置存在问题',
]

// getConfigInfo() 返回
[
    'enabled' => true,
    'max_records' => 100000,
    'connection' => 'dblog',
    'table' => 'sys_request_logs',
]

// getCurrentLogCount() 返回
72
```

---

## 六、集成建议

### 在系统设置页面添加

编辑 `Modules/DcatAdmin/DcatAdmin/Controllers/SystemController.php`：

```php
public function index(Content $content)
{
    return $content
        ->title('系统设置')
        ->body(function (Row $row) {
            // 请求日志配置检查
            $row->column(6, new RequestLogConfigCheckMetric());

            // 其他配置检查...
        });
}
```

### 在健康检查页面添加

创建健康检查页面，集中展示所有配置检查：

```php
public function healthCheck(Content $content)
{
    return $content
        ->title('系统健康检查')
        ->body(function (Row $row) {
            // 请求日志配置检查
            $row->column(6, new RequestLogConfigCheckMetric());

            // 文件存储配置检查
            $row->column(6, new FileStorageConfigCheckMetric());

            // 其他检查...
        });
}
```

---

## 七、注意事项

### 1. 配置优先级

- `REQUEST_LOG_ENABLED=false` → 不检查，显示"未启用"
- `REQUEST_LOG_ENABLED=true` → 执行完整检查

### 2. 独立数据库建议

检查服务会提示 `dblog` 与主数据库是否相同：

- 相同数据库：显示警告（建议独立）
- 独立数据库：显示成功

### 3. 自动修复

部分问题可以自动修复（未来功能）：

- 自动创建数据库
- 自动执行迁移
- 自动更新配置

---

## 八、相关文档

- [请求日志数据库存储方案](../../AiWork/202608/24/24-1456-请求日志数据库存储方案.md)
- [请求日志配置快速指南](../../AiWork/202608/24/24-1510-请求日志配置快速指南.md)
- [dblog数据库连接配置方案](../../AiWork/202608/24/24-1500-dblog数据库连接配置方案.md)

---

**文档维护**: AI 开发团队
**最后更新**: 2026-08-24