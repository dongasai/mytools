# 文件存储配置检查工具

## 功能说明

在超管后台首页添加了一个自动检查工具，用于检测 `file_storage_configs` 表中的配置项是否正确。

## 架构设计

采用标准的**分层架构**：

```
Metric (展示层)
  ↓ 调用
Service (业务层)
  ↓ 查询
Database (数据层)
```

**优势**：
- Metric 只负责渲染 HTML，符合单一职责原则
- Service 可复用（其他地方也可以调用检查逻辑）
- 易于测试和维护

## 检查项

该工具会自动检查以下内容：

### 1. 路径有效性检查
- 检查本地存储（local driver）的路径是否在当前项目目录内
- 检查路径是否真实存在

### 2. 默认存储检查
- 检查默认存储的路径是否正确（应为 `storage_path('app')`）
- 检查是否有且仅有一个默认存储配置

### 3. 配置完整性检查
- 检查必要字段是否缺失

## 显示位置

工具卡片位于：超管后台首页 → 最底部（全宽显示）

## 状态显示

### 正常状态
- **✓ 所有配置项正常**：绿色显示，表示配置无误
- 无额外操作按钮

### 异常状态
- **✗ 发现 N 个问题**：红色显示，列出所有问题详情
- **前往修复按钮**：黄色按钮，点击跳转到存储配置列表
- 快速定位问题配置，一键修复

## 常见问题处理

### 问题：路径不在项目目录内

**原因**：数据库中保存了错误的硬编码路径（如从其他项目复制的数据）

**解决方法**：
```php
php artisan tinker --execute="
DB::table('file_storage_configs')->where('id', 1)->update([
    'config' => json_encode([
        'root' => storage_path('app'),
        'throw' => true
    ])
]);
echo '已更新 ID=1 的存储路径';
"
```

### 问题：路径不存在

**原因**：目录未创建

**解决方法**：
```bash
mkdir -p storage/app/private
mkdir -p storage/app/public
```

### 问题：没有设置默认文件存储

**解决方法**：
```php
php artisan tinker --execute="
DB::table('file_storage_configs')->where('name', 'local')->update(['is_default' => 1]);
echo '已设置默认存储';
"
```

## 实现文件

- **Service 业务层**：`Modules/AFile/Services/FileStorageConfigCheckService.php`
- **Metric 展示层**：`Modules/AFile/DcatAdmin/Metrics/FileStorageConfigCheckMetric.php`
- **首页控制器**：`Modules/NtMain/DcatAdmin/Controllers/HomeController.php`

## 技术要点

1. **分层架构**：Service 处理业务逻辑，Metric 只负责渲染
2. **静态方法**：Service 采用静态方法设计，符合项目规范
3. **异步加载**：Metric 异步加载数据，不阻塞页面渲染
4. **异常捕获**：Service 捕获异常，避免错误影响首页显示
5. **withContent 包装**：Metric 使用 `withContent()` 方法包装 HTML，符合 Dcat Admin 规范

## 扩展建议

### 添加更多检查项

在 `FileStorageConfigCheckService::checkConfigs()` 方法中扩展：

```php
// 检查 OSS 配置
if ($config->driver === 'oss') {
    if (empty($configData['access_key_id']) || empty($configData['access_key_secret'])) {
        $issues[] = sprintf('[%s] OSS 配置不完整', $config->name);
    }
}

// 检查 S3 配置
if ($config->driver === 's3') {
    if (empty($configData['key']) || empty($configData['bucket'])) {
        $issues[] = sprintf('[%s] S3 配置不完整', $config->name);
    }
}
```

### 独立使用 Service

Service 可以在任何地方调用：

```php
use Modules\NtMain\Services\FileStorageConfigCheckService;

// 获取问题数量
$count = FileStorageConfigCheckService::getIssueCount();

// 获取完整检查结果
$summary = FileStorageConfigCheckService::getSummary();

// 直接获取问题列表
$issues = FileStorageConfigCheckService::checkConfigs();
```

---

**创建时间**：2026-08-18
**维护者**：AI 开发团队
## 使用流程

### 完整修复流程

```
首页检查卡片（发现问题）
    ↓ 点击"前往修复"
存储配置列表（定位问题行）
    ↓ 点击"修复配置"
自动修复（修正路径）
    ↓ 刷新页面
首页检查卡片（确认修复）
```

### 1. 首页检查
访问后台首页，查看"文件存储配置检查"卡片：
- **正常**：显示绿色 ✓，无需操作
- **异常**：显示红色 ✗ + 问题列表 + "前往修复"按钮

### 2. 快速跳转
点击黄色的"前往修复"按钮，直接跳转到存储配置管理列表

### 3. 定位问题
在列表中，有问题的配置行会显示黄色的"修复配置"按钮

### 4. 执行修复
点击"修复配置"按钮 → 确认操作 → 自动修正路径 → 页面刷新

### 5. 验证结果
返回首页，确认检查卡片显示绿色 ✓ "所有配置项正常"

## 两个工具的协作

### 首页检查工具（全局）
- **位置**：后台首页
- **作用**：实时监控，快速发现问题
- **功能**：显示问题摘要 + 快速跳转链接
- **特点**：无需主动检查，访问首页自动显示

### 列表修复工具（单条）
- **位置**：存储配置列表
- **作用**：精准定位，逐条修复
- **功能**：自动检测 + 智能修复
- **特点**：条件显示，避免误操作

### 配合使用
1. **首页发现问题** → 查看问题列表
2. **点击跳转** → 进入配置列表
3. **定位问题行** → 看到修复按钮
4. **一键修复** → 自动修正配置
5. **返回验证** → 确认问题解决

---

**更新时间**：2026-08-18
**维护者**：AI 开发团队
