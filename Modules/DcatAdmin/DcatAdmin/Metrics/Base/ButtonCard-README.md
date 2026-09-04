# ButtonCard 基类使用指南

## 概述

`ButtonCard` 是按钮卡片的基类，封装了按钮卡片的通用逻辑，简化开发流程。

---

## 核心优势

使用 `ButtonCard` 基类后，开发者只需要：

1. ✅ 定义按钮配置（`getButtons()`）
2. ✅ 实现业务逻辑（`executeAction()`）
3. ✅ 渲染内容（`withContent()`）

无需关注：
- ❌ 事件绑定逻辑
- ❌ 确认对话框处理
- ❌ handle() 处理流程
- ❌ addScript() 脚本编写

---

## 快速开始

### 示例1：单按钮卡片（清理缓存）

```php
<?php

namespace Modules\Application\DcatAdmin\Metrics;

use Modules\DcatAdmin\DcatAdmin\Metrics\Base\ButtonCard;

class CacheClearMetric extends ButtonCard
{
    protected $height = 160;

    protected function init()
    {
        parent::init();
        $this->title('缓存管理');
    }

    /**
     * 定义按钮配置
     */
    protected function getButtons(): array
    {
        return [
            [
                'action' => 'clear',
                'label' => '清理缓存',
                'type' => 'primary',
                'icon' => 'fa-refresh',
                'confirm' => '确定要清理所有缓存吗？',
                'confirmTitle' => '清理确认'
            ]
        ];
    }

    /**
     * 执行业务逻辑
     */
    protected function executeAction(string $action): string
    {
        if ($action === 'clear') {
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            return '缓存清理成功！';
        }
        return '';
    }

    /**
     * 渲染卡片内容
     */
    public function withContent(?string $message)
    {
        $driverCount = count(config('cache.stores'));
        $defaultDriver = config('cache.default');

        return $this->content(
            <<<HTML
<div class="d-flex flex-column flex-wrap text-center">
    {$this->renderAlert($message)}
    <div class="mb-3">
        <i class="fa fa-database fa-2x text-primary"></i>
    </div>
    <h5 class="font-weight-bold mb-2">缓存管理</h5>
    <p class="text-muted mb-2">已配置 {$driverCount} 个缓存驱动，当前使用：{$defaultDriver}</p>
    {$this->renderButtons()}
</div>
HTML
        );
    }
}
```

### 示例2：多按钮卡片（数据操作）

```php
<?php

namespace Modules\YourModule\DcatAdmin\Metrics;

use Modules\DcatAdmin\DcatAdmin\Metrics\Base\ButtonCard;

class DataOperationMetric extends ButtonCard
{
    protected function init()
    {
        parent::init();
        $this->title('数据操作');
    }

    protected function getButtons(): array
    {
        return [
            [
                'action' => 'reset',
                'label' => '重置',
                'type' => 'warning',
                'icon' => 'fa-refresh',
                'confirm' => '确定要重置所有数据吗？',
                'confirmTitle' => '重置确认'
            ],
            [
                'action' => 'sync',
                'label' => '同步',
                'type' => 'success',
                'icon' => 'fa-sync',
                'confirm' => '确定要同步最新数据吗？',
                'confirmTitle' => '同步确认'
            ]
        ];
    }

    protected function executeAction(string $action): string
    {
        switch ($action) {
            case 'reset':
                // 执行重置逻辑
                \Log::info('数据重置');
                return '重置成功！';

            case 'sync':
                // 执行同步逻辑
                \Log::info('数据同步');
                return '同步成功！';
        }

        return '';
    }

    public function withContent(?string $message)
    {
        $data = $this->getData();

        return $this->content(
            <<<HTML
<div class="d-flex flex-column flex-wrap text-center">
    {$this->renderAlert($message)}
    <div class="d-flex justify-content-around mb-3">
        <div>
            <div class="font-lg-1 text-primary">{$data['total']}</div>
            <small class="text-muted">总数</small>
        </div>
        <div>
            <div class="font-lg-1 text-success">{$data['active']}</div>
            <small class="text-muted">活跃</small>
        </div>
    </div>
    {$this->renderButtons()}
</div>
HTML
        );
    }

    private function getData(): array
    {
        return [
            'total' => 100,
            'active' => 80
        ];
    }
}
```

---

## API 文档

### 抽象方法（必须实现）

#### `getButtons(): array`

定义按钮配置。

**返回格式**：
```php
[
    [
        'action' => 'clear',           // 必须：操作标识
        'label' => '清理缓存',          // 必须：按钮文字
        'type' => 'primary',           // 可选：按钮类型（默认 primary）
        'icon' => 'fa-refresh',        // 可选：图标类名
        'confirm' => '确定清理？',      // 可选：确认提示（默认 "确定执行此操作？"）
        'confirmTitle' => '清理确认',   // 可选：确认标题（默认 "操作确认"）
    ]
]
```

**按钮类型**：
- `primary` - 蓝色（主要操作）
- `success` - 绿色（成功操作）
- `warning` - 橙色（警告操作）
- `danger` - 红色（危险操作）
- `info` - 浅蓝（信息操作）

#### `executeAction(string $action): string`

执行按钮操作的业务逻辑。

**参数**：
- `$action` - 操作标识，来自 `getButtons()` 中定义的 `action` 字段

**返回**：
- 成功消息字符串

#### `withContent(?string $message)`

渲染卡片内容。

**参数**：
- `$message` - 操作结果消息（来自 `executeAction()`）

---

### 辅助方法（可直接调用）

#### `renderButtons(): string`

渲染按钮 HTML。

**自动处理**：
- 单按钮：渲染单个按钮
- 多按钮：渲染 `btn-group` 按钮组

**示例**：
```php
public function withContent(?string $message)
{
    return $this->content(
        <<<HTML
<div class="text-center">
    <h5>卡片标题</h5>
    {$this->renderButtons()}
</div>
HTML
    );
}
```

#### `renderAlert(?string $message, string $type = 'success'): string`

渲染提示消息。

**参数**：
- `$message` - 消息内容
- `$type` - 提示类型（`success`/`danger`/`warning`/`info`）

**示例**：
```php
public function withContent(?string $message)
{
    return $this->content(
        <<<HTML
<div class="text-center">
    {$this->renderAlert($message)}
    <h5>卡片标题</h5>
</div>
HTML
    );
}
```

---

## 完整配置示例

```php
<?php

namespace Modules\Application\DcatAdmin\Metrics;

use Modules\DcatAdmin\DcatAdmin\Metrics\Base\ButtonCard;
use Illuminate\Support\Facades\Artisan;

class CacheClearMetric extends ButtonCard
{
    protected $height = 160;

    protected function init()
    {
        parent::init();
        $this->title('缓存管理');
    }

    /**
     * 定义按钮配置
     */
    protected function getButtons(): array
    {
        return [
            [
                'action' => 'clear',
                'label' => '清理缓存',
                'type' => 'primary',
                'icon' => 'fa-refresh',
                'confirm' => '确定要清理所有缓存吗？',
                'confirmTitle' => '清理确认'
            ]
        ];
    }

    /**
     * 执行业务逻辑
     */
    protected function executeAction(string $action): string
    {
        if ($action === 'clear') {
            Artisan::call('cache:clear');
            return '缓存清理成功！';
        }

        return '';
    }

    /**
     * 渲染卡片内容
     */
    public function withContent(?string $message)
    {
        $driverCount = count(config('cache.stores'));
        $defaultDriver = config('cache.default');

        return $this->content(
            <<<HTML
<div class="d-flex flex-column flex-wrap text-center">
    {$this->renderAlert($message)}
    <div class="mb-3">
        <i class="fa fa-database fa-2x text-primary"></i>
    </div>
    <h5 class="font-weight-bold mb-2">缓存管理</h5>
    <p class="text-muted mb-2">已配置 {$driverCount} 个缓存驱动，当前使用：{$defaultDriver}</p>
    {$this->renderButtons()}
</div>
HTML
        );
    }
}
```

---

## 对比：使用前 vs 使用后

### 使用前（继承 Card）

```php
class CacheClearMetric extends Card
{
    protected function init()
    {
        parent::init();
        $this->title('缓存管理');
    }

    public function handle(Request $request)
    {
        $message = null;
        if ($request->get('action') === 'clear') {
            Artisan::call('cache:clear');
            $message = '缓存清理成功！';
        }
        $this->withContent($message);
    }

    public function withContent(?string $message)
    {
        // ... 渲染内容
    }

    public function addScript()
    {
        $id = $this->id();

        $this->fetching(<<<JS
var card = $('#{$id}');
card.loading();
JS
        );

        $this->fetched(<<<JS
card.loading(false);
card.find('.metric-header').html(response.header);
card.find('.metric-content').html(response.content);

card.find('.action-btn').off('click').on('click', function(e) {
    e.preventDefault();
    var btn = $(this);
    var data = btn.data();

    Dcat.confirm('确定清理缓存？', '', function() {
        request(data);
    });
});
JS
        );

        $clickable = "#{$id} .action-btn";
        $this->click($clickable);
        $this->script = $this->buildRequestScript();

        return $this->script;
    }
}
```

### 使用后（继承 ButtonCard）

```php
class CacheClearMetric extends ButtonCard
{
    protected function init()
    {
        parent::init();
        $this->title('缓存管理');
    }

    protected function getButtons(): array
    {
        return [
            ['action' => 'clear', 'label' => '清理缓存', 'type' => 'primary']
        ];
    }

    protected function executeAction(string $action): string
    {
        if ($action === 'clear') {
            Artisan::call('cache:clear');
            return '缓存清理成功！';
        }
        return '';
    }

    public function withContent(?string $message)
    {
        // ... 渲染内容（使用 $this->renderButtons() 和 $this->renderAlert()）
    }
}
```

**减少代码量**：~50%

---

## 最佳实践

### 1. 按钮命名规范

使用清晰的动词：
- ✅ `clear` - 清理
- ✅ `sync` - 同步
- ✅ `reset` - 重置
- ✅ `export` - 导出
- ✅ `refresh` - 刷新

### 2. 确认提示规范

清晰说明操作后果：
- ✅ "确定要清理所有缓存吗？"
- ✅ "这将重置所有数据到初始状态"
- ❌ "确定？"

### 3. 按钮类型选择

根据操作风险选择：
- 主要操作 → `primary`（蓝色）
- 成功操作 → `success`（绿色）
- 警告操作 → `warning`（橙色）
- 危险操作 → `danger`（红色）

---

## 常见问题

### 如何自定义按钮样式？

修改 `getButtons()` 返回的配置：
```php
[
    'action' => 'clear',
    'label' => '清理缓存',
    'type' => 'danger',  // 红色按钮
    'size' => 'lg',      // 大号按钮
    'icon' => 'fa-trash' // 图标
]
```

### 如何添加数据展示？

在 `withContent()` 中渲染数据：
```php
public function withContent(?string $message)
{
    $data = $this->getData();

    return $this->content(<<<HTML
<div>
    {$this->renderAlert($message)}
    <div>总数：{$data['total']}</div>
    {$this->renderButtons()}
</div>
HTML
    );
}
```

### 如何处理复杂业务逻辑？

在 `executeAction()` 中调用 Service 层：
```php
protected function executeAction(string $action): string
{
    return match($action) {
        'sync' => YourService::sync(),
        'reset' => YourService::reset(),
        default => ''
    };
}
```

---

## 相关文档

- `docs/Dcat-Admin-Metric按钮卡片开发经验.md`
- `Modules/DcatAdmin/DcatAdmin/Metrics/Examples/Metric按钮卡片示例对比.md`

---

**更新时间**：2026-08-21