# Learning 学习目录

本目录包含用于学习理解 Metric 按钮卡片原理的示例文件。

---

## 文件说明

| 文件 | 说明 | 学习重点 |
|------|------|---------|
| ActionButtonMetric.php | 多按钮卡片 | 多按钮处理、事件绑定、确认对话框 |
| SingleButtonMetric.php | 单按钮卡片 | 最简单的按钮卡片实现 |
| ActionButtonMetric-README.md | 多按钮说明文档 | - |
| SingleButtonMetric-README.md | 单按钮说明文档 | - |

---

## 学习目的

这两个文件展示了**如何从零开始实现按钮卡片**，包括：

1. **事件绑定机制**：在 `fetched` 回调中重新绑定事件
2. **参数传递**：通过 `data-*` 属性传递参数
3. **确认对话框**：使用 `Dcat.confirm()` 显示确认提示
4. **handle() 处理流程**：如何处理异步请求

---

## 实际开发建议

⚠️ **不建议直接使用这些文件进行开发**

实际开发时，推荐使用：

```php
// ✅ 推荐：继承 ButtonCard 基类
class MyMetric extends ButtonCard
{
    // 只需实现 3 个方法
    protected function getButtons(): array { }
    protected function executeAction(string $action): string { }
    public function withContent(?string $message) { }
}
```

详见：`Modules/DcatAdmin/DcatAdmin/Metrics/Base/ButtonCard-README.md`

---

## 学习路径

1. **先看** `SingleButtonMetric.php`
   - 理解基本的事件绑定机制
   - 理解 `fetched` 回调的作用
   - 理解参数传递

2. **再看** `ActionButtonMetric.php`
   - 理解多按钮处理
   - 理解不同确认信息
   - 理解数据展示

3. **最后理解** `ButtonCard` 基类
   - 看基类如何封装这些逻辑
   - 学习如何使用基类简化开发

---

**更新时间**：2026-08-21