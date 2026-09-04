# 单按钮卡片示例使用指南

## 示例位置

`Modules/DcatAdmin/DcatAdmin/Metrics/Examples/SingleButtonMetric.php`

## 访问方式

访问：`/admin/module_dcatadmin/metrics`

在图表演示页面可以看到"单按钮卡片"示例。

## 功能演示

### 1. 卡片显示

- 卡片标题和图标
- 简单的说明文字
- 单个操作按钮

### 2. 点击操作

- 点击"执行操作"按钮 → 弹出确认框 → 确认后执行
- 显示 loading 效果
- 执行完成后显示成功消息

### 3. 操作结果

- 成功消息提示
- 自动刷新卡片内容

## 核心代码解析

### 关键实现

```php
// 1. 简单的 handle() 处理逻辑
public function handle(Request $request)
{
    if ($request->get('action') === 'execute') {
        // 执行业务逻辑
        $message = '操作执行成功！';
    }

    $this->withContent($message ?? null);
}

// 2. 简洁的内容渲染
public function withContent(?string $message)
{
    return $this->content(<<<HTML
<div class="text-center">
    {/* 消息提示 */}
    <button class="btn btn-primary action-btn" data-action="execute">
        执行操作
    </button>
</div>
HTML
    );
}

// 3. 在 fetched 中重新绑定事件
$this->fetched(<<<JS
card.find('.action-btn').off('click').on('click', function(e) {
    e.preventDefault();
    var data = $(this).data();

    Dcat.confirm('确定要执行操作吗？', '说明', function() {
        request(data);
    });
});
JS
);
```

## 如何使用

### 在控制器中引入

```php
use Modules\DcatAdmin\DcatAdmin\Metrics\Examples\SingleButtonMetric;

public function index(Content $content)
{
    return $content->body(function (Row $row) {
        $row->column(4, new SingleButtonMetric());
    });
}
```

### 自定义操作

修改 `handle()` 方法中的逻辑：

```php
public function handle(Request $request)
{
    if ($request->get('action') === 'execute') {
        // 替换为你的业务逻辑
        // 例如：
        // - 清理缓存
        // - 同步数据
        // - 发送通知
        // - 重置状态

        $message = '操作成功！';
    }

    $this->withContent($message ?? null);
}
```

## 与 ActionButtonMetric 的区别

| 特性 | SingleButtonMetric | ActionButtonMetric |
|------|-------------------|-------------------|
| 按钮数量 | 1个 | 2个 |
| 数据展示 | 无 | 有（总数/活跃/待处理） |
| 复杂度 | 最简单 | 中等 |
| 适用场景 | 单一操作 | 多个操作选择 |

## 相关文档

详细经验总结：`docs/Dcat-Admin-Metric按钮卡片开发经验.md`

双按钮示例：`ActionButtonMetric.php`

---
**访问测试**：`http://nengtan.local.xiaobei.fun/admin/module_dcatadmin/metrics`