# 按钮卡片示例使用指南

## 示例位置

`Modules/DcatAdmin/DcatAdmin/Metrics/Examples/ActionButtonMetric.php`

## 访问方式

访问：`/admin/module_dcatadmin/metrics`

在图表演示页面可以看到"操作按钮示例"卡片。

## 功能演示

### 1. 卡片显示

- 数据概览（总数、活跃、待处理）
- 两个操作按钮：重置、同步

### 2. 点击操作

- 点击"重置"按钮 → 弹出确认框 → 确认后执行
- 点击"同步"按钮 → 弹出确认框 → 确认后执行

### 3. 操作结果

- 显示成功消息
- 自动刷新卡片内容

## 核心代码解析

### 关键实现

```php
// 1. 在 fetched 回调中重新绑定事件
$this->fetched(
    <<<JS
card.find('.metric-content').html(response.content);

// 关键：重新绑定按钮点击事件
card.find('.action-btn').off('click').on('click', function(e) {
    e.preventDefault();
    var btn = $(this);
    var data = btn.data();

    Dcat.confirm('确定操作吗？', '说明', function() {
        request(data);  // 发送请求
    });
});
JS
);

// 2. 正确赋值 script
$this->script = $this->buildRequestScript();
return $this->script;
```

## 如何使用

### 在控制器中引入

```php
use Modules\DcatAdmin\DcatAdmin\Metrics\Examples\ActionButtonMetric;

public function index(Content $content)
{
    return $content->body(function (Row $row) {
        $row->column(4, new ActionButtonMetric());
    });
}
```

### 自定义操作

修改 `handle()` 方法中的逻辑：

```php
public function handle(Request $request)
{
    $action = $request->get('action');

    if ($action === 'your-action') {
        // 执行你的业务逻辑
        $message = '操作成功！';
    }

    $this->withContent($message ?? null, $data);
}
```

## 相关文档

详细经验总结：`docs/Dcat-Admin-Metric按钮卡片开发经验.md`

实际应用案例：`Modules/Application/DcatAdmin/Metrics/CacheClearMetric.php`

---

**访问测试**：`http://nengtan.local.xiaobei.fun/admin/module_dcatadmin/metrics`