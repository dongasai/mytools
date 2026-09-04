# Metric 按钮卡片示例对比

本文档对比四个按钮卡片示例，帮助选择合适的实现方式。

---

## 示例总览

| 示例 | 文件 | 按钮数 | 数据展示 | 复杂度 | 基类 | 适用场景 |
|------|------|--------|----------|--------|------|----------|
| **Tickets** | `Tickets.php` | 无（纯展示） | 环形图+4数字 | 中等 | `RadialBar` | 数据概览 |
| **SingleButtonCardExample** | `Examples/SingleButtonCardExample.php` | 1个 | 无 | 最简单 | `ButtonCard` | **单按钮操作** |
| **MultiButtonCardExample** | `Examples/MultiButtonCardExample.php` | 2个 | 有（3数字） | 简单 | `ButtonCard` | **多按钮操作** |
| **ActionButtonMetric** | `Learning/ActionButtonMetric.php` | 2个 | 有（3数字） | 中等 | `Card` | 学习理解原理 |
| **SingleButtonMetric** | `Learning/SingleButtonMetric.php` | 1个 | 无 | 最简单 | `Card` | 学习理解原理 |

---

## 一、Tickets 示例

**文件**：`Modules/DcatAdmin/DcatAdmin/Metrics/Examples/Tickets.php`

**特点**：
- 纯数据展示，无按钮操作
- 环形进度图 + 底部4个数字统计
- 使用 `RadialBar` 基类

**适用场景**：
- 数据概览
- 状态监控
- 无需交互操作

**核心代码**：
```php
class Tickets extends RadialBar
{
    protected function init()
    {
        parent::init();
        $this->title('工单统计');
    }

    public function render()
    {
        $this->fill();
        return parent::render();
    }

    public function fill()
    {
        // 设置内容
        $this->withContent(100, 50, 30, 20);

        // 设置图表
        $this->withChart(75);
    }
}
```

---

## 二、SingleButtonCardExample 示例（推荐）

**文件**：`Modules/DcatAdmin/DcatAdmin/Metrics/Examples/SingleButtonCardExample.php`

**特点**：
- 1个操作按钮
- 无数据展示
- 代码最简洁
- 继承 `ButtonCard` 基类

**适用场景**：
- 清理缓存
- 同步数据
- 发送通知
- 单一确认操作

**核心代码**：
```php
class SingleButtonCardExample extends ButtonCard
{
    protected function getButtons(): array
    {
        return [['action' => 'execute', 'label' => '执行操作']];
    }

    protected function executeAction(string $action): string
    {
        if ($action === 'execute') {
            // 业务逻辑
            return '操作成功！';
        }
        return '';
    }

    public function withContent(?string $message)
    {
        return $this->content(<<<HTML
<div>
    {$this->renderAlert($message)}
    {$this->renderButtons()}
</div>
HTML
        );
    }
}
```

---

## 三、MultiButtonCardExample 示例（推荐）

**文件**：`Modules/DcatAdmin/DcatAdmin/Metrics/Examples/MultiButtonCardExample.php`

**特点**：
- 2个操作按钮（重置、同步）
- 数据概览（总数/活跃/待处理）
- 不同类型的按钮
- 继承 `ButtonCard` 基类

**适用场景**：
- 数据管理（重置、同步）
- 批量操作（导出、删除）
- 多个操作选择

**核心代码**：
```php
class MultiButtonCardExample extends ButtonCard
{
    protected function getButtons(): array
    {
        return [
            ['action' => 'reset', 'label' => '重置', 'type' => 'warning'],
            ['action' => 'sync', 'label' => '同步', 'type' => 'success']
        ];
    }

    protected function executeAction(string $action): string
    {
        switch ($action) {
            case 'reset':
                // 重置逻辑
                return '重置成功！';
            case 'sync':
                // 同步逻辑
                return '同步成功！';
        }
        return '';
    }
}
```

---

## 四、ActionButtonMetric 示例（学习用）

**文件**：`Modules/DcatAdmin/DcatAdmin/Metrics/Learning/ActionButtonMetric.php`

**特点**：
- 2个操作按钮（重置、同步）
- 数据概览（总数/活跃/待处理）
- 每个按钮不同的确认信息
- 继承 `Card` 基类

**适用场景**：
- **学习理解按钮卡片原理**
- 理解事件绑定机制
- 理解多按钮处理

> ⚠️ 实际开发请使用 `ButtonCard` 基类

**核心代码**：
```php
class ActionButtonMetric extends Card
{
    public function handle(Request $request)
    {
        $action = $request->get('action');

        if ($action === 'reset') {
            $message = '重置成功！';
        } elseif ($action === 'sync') {
            $message = '同步成功！';
        }

        $this->withContent($message ?? null, $this->getCardData());
    }

    public function withContent(?string $message, array $data)
    {
        return $this->content(<<<HTML
<div>
    {/* 数据概览 */}
    <div class="btn-group">
        <button class="btn action-btn" data-action="reset">重置</button>
        <button class="btn action-btn" data-action="sync">同步</button>
    </div>
</div>
HTML
        );
    }

    public function addScript()
    {
        $this->fetched(<<<JS
// 根据不同操作显示不同确认信息
var messages = {
    'reset': { title: '确定重置？', content: '将重置所有数据' },
    'sync': { title: '确定同步？', content: '将同步最新数据' }
};

card.find('.action-btn').off('click').on('click', function(e) {
    var action = $(this).data('action');
    var msg = messages[action];

    Dcat.confirm(msg.title, msg.content, function() {
        request({action: action});
    });
});
JS
        );
    }
}
```

---

## 五、SingleButtonMetric 示例（学习用）

**文件**：`Modules/DcatAdmin/DcatAdmin/Metrics/Learning/SingleButtonMetric.php`

**特点**：
- 1个操作按钮
- 无数据展示
- 代码最简洁
- 继承 `Card` 基类

**适用场景**：
- **学习理解按钮卡片原理**
- 理解基本的事件绑定
- 理解最简单的实现方式

> ⚠️ 实际开发请使用 `ButtonCard` 基类

**核心代码**：
```php
class SingleButtonMetric extends Card
{
    public function handle(Request $request)
    {
        if ($request->get('action') === 'execute') {
            // 执行业务逻辑
            $message = '操作成功！';
        }

        $this->withContent($message ?? null);
    }

    public function withContent(?string $message)
    {
        return $this->content(<<<HTML
<div class="text-center">
    <button class="btn btn-primary action-btn" data-action="execute">
        执行操作
    </button>
</div>
HTML
        );
    }

    public function addScript()
    {
        $this->fetched(<<<JS
card.find('.action-btn').off('click').on('click', function(e) {
    Dcat.confirm('确定执行？', '说明', function() {
        request({action: 'execute'});
    });
});
JS
        );
    }
}
```

---

## 四、ButtonCardExample 示例（推荐）

**文件**：`Modules/DcatAdmin/DcatAdmin/Metrics/Examples/ButtonCardExample.php`

**特点**：
- 2个操作按钮
- 数据概览展示
- 代码最简洁（使用 `ButtonCard` 基类）
- 继承 `ButtonCard` 基类

**适用场景**：
- 所有需要按钮操作的场景（推荐）
- 需要快速开发
- 需要数据展示 + 操作控制

**核心代码**：
```php
class ButtonCardExample extends ButtonCard
{
    // 1. 定义按钮配置
    protected function getButtons(): array
    {
        return [
            ['action' => 'reset', 'label' => '重置', 'type' => 'warning'],
            ['action' => 'sync', 'label' => '同步', 'type' => 'success']
        ];
    }

    // 2. 实现业务逻辑
    protected function executeAction(string $action): string
    {
        switch ($action) {
            case 'reset':
                // 重置逻辑
                return '重置成功！';
            case 'sync':
                // 同步逻辑
                return '同步成功！';
        }
        return '';
    }

    // 3. 渲染内容
    public function withContent(?string $message)
    {
        return $this->content(<<<HTML
<div>
    {$this->renderAlert($message)}
    {/* 数据展示 */}
    {$this->renderButtons()}
</div>
HTML
        );
    }
}
```

**优势**：
- ✅ 无需编写 `addScript()`（基类已封装）
- ✅ 无需处理事件绑定（基类已封装）
- ✅ 无需编写 `handle()`（基类已封装）
- ✅ 提供辅助方法 `renderButtons()` 和 `renderAlert()`
- ✅ 代码量减少 ~50%

---

## 六、如何选择

### 需求决策树

```
需要按钮操作？
├── 否 → Tickets（纯数据展示）
└── 是 → 需要几个按钮？
    ├── 1个 → SingleButtonCardExample（推荐）
    └── 多个 → MultiButtonCardExample（推荐）
```

### 详细选择依据

| 需求 | 推荐示例 | 原因 |
|------|---------|------|
| 只展示数据，无操作 | Tickets | 专门的数据展示卡片 |
| 单一操作（清理缓存、同步） | **SingleButtonCardExample** | 代码最简洁，适合单按钮 |
| 多个操作（重置、同步、导出） | **MultiButtonCardExample** | 支持多按钮和数据展示 |
| 学习按钮卡片原理 | SingleButtonMetric | 代码简单，便于理解 |
| 学习多按钮处理 | ActionButtonMetric | 展示完整的事件绑定流程 |

---

## 七、实现差异对比

### 7.1 基类继承

| 示例 | 基类 | 按钮数 | 推荐度 |
|------|------|--------|--------|
| Tickets | `RadialBar` | 无 | ⭐⭐⭐ |
| **SingleButtonCardExample** | **`ButtonCard`** | **1个** | **⭐⭐⭐⭐⭐** |
| **MultiButtonCardExample** | **`ButtonCard`** | **2个** | **⭐⭐⭐⭐⭐** |
| ActionButtonMetric | `Card` | 2个 | ⭐（学习用） |
| SingleButtonMetric | `Card` | 1个 | ⭐（学习用） |

### 7.2 handle() 方法

| 示例 | 处理逻辑 | 需要实现 |
|------|---------|----------|
| Tickets | 无（使用 `fill()` 方法） | 否 |
| **SingleButtonCardExample** | **基类已封装** | **否** |
| **MultiButtonCardExample** | **基类已封装** | **否** |
| ActionButtonMetric | 多分支判断 | 是 |
| SingleButtonMetric | 单分支判断 | 是 |

### 7.3 事件绑定

| 示例 | 事件绑定复杂度 | 需要编写 |
|------|---------------|----------|
| Tickets | 无按钮事件 | 否 |
| **SingleButtonCardExample** | **基类已封装** | **否** |
| **MultiButtonCardExample** | **基类已封装** | **否** |
| ActionButtonMetric | 多按钮 + 不同确认信息 | 是（~30行） |
| SingleButtonMetric | 单按钮 + 固定确认信息 | 是（~20行） |

### 7.4 代码量对比

| 示例 | 代码行数 | 复杂度 |
|------|---------|--------|
| Tickets | ~50行 | 中等 |
| **SingleButtonCardExample** | **~60行** | **最低** |
| **MultiButtonCardExample** | **~80行** | **低** |
| ActionButtonMetric | ~180行 | 较高 |
| SingleButtonMetric | ~135行 | 中等 |

**使用 ButtonCard 基类相比直接继承 Card 减少代码量 ~56%**

---

## 八、最佳实践

### 8.1 选择原则

1. **按需选择**：根据按钮数量选择对应的示例
2. **优先使用基类**：单按钮和多按钮都推荐使用 ButtonCard 基类
3. **学习路径**：先看 Learning 目录理解原理，再用 Examples 开发

### 8.2 开发流程

**单按钮开发流程**：
```bash
# 1. 复制 SingleButtonCardExample
cp SingleButtonCardExample.php MyMetric.php

# 2. 修改配置
# - 修改 title
# - 修改 getButtons() 定义按钮
# - 修改 executeAction() 实现业务
# - 修改 withContent() 渲染内容
```

**多按钮开发流程**：
```bash
# 1. 复制 MultiButtonCardExample
cp MultiButtonCardExample.php MyMetric.php

# 2. 修改配置
# - 修改 title
# - 修改 getButtons() 定义多个按钮
# - 修改 executeAction() 处理多个操作
# - 修改 withContent() 渲染内容（可添加数据展示）
```

---

## 七、常见问题

### 7.1 按钮点击无反应？

**原因**：动态内容事件绑定问题

**解决**：在 `fetched` 回调中重新绑定事件
```php
$this->fetched(<<<JS
card.find('.action-btn').off('click').on('click', function(e) {
    // ...
});
JS
);
```

### 7.2 如何添加数据展示？

参考 `ActionButtonMetric` 的 `withContent()` 方法：
```php
public function withContent(?string $message, array $data)
{
    return $this->content(<<<HTML
<div>
    {/* 数据统计 */}
    <div>{$data['total']}</div>

    {/* 操作按钮 */}
    <button class="btn action-btn">操作</button>
</div>
HTML
    );
}
```

### 7.3 如何处理多个按钮？

参考 `ActionButtonMetric` 的多操作处理：
```php
public function handle(Request $request)
{
    $action = $request->get('action');

    switch ($action) {
        case 'action1':
            // 处理操作1
            break;
        case 'action2':
            // 处理操作2
            break;
    }
}
```

---

## 九、访问测试

- **地址**：`http://nengtan.local.xiaobei.fun/admin/module_dcatadmin/metrics`
- **位置**：图表演示页面
- **测试内容**：
  1. 查看 Tickets 的数据展示
  2. 测试 SingleButtonCardExample 的单按钮操作
  3. 测试 MultiButtonCardExample 的多按钮操作
  4. 查看 Learning 目录的学习文件理解原理

---

## 十、ButtonCard 基类 API

详见：`Modules/DcatAdmin/DcatAdmin/Metrics/Base/ButtonCard-README.md`

### 核心方法

| 方法 | 类型 | 说明 |
|------|------|------|
| `getButtons()` | 抽象 | 定义按钮配置 |
| `executeAction()` | 抽象 | 实现业务逻辑 |
| `withContent()` | 抽象 | 渲染卡片内容 |
| `renderButtons()` | 辅助 | 渲染按钮 HTML |
| `renderAlert()` | 辅助 | 渲染提示消息 |

---

**更新时间**：2026-08-21
**相关文档**：
- `docs/Dcat-Admin-Metric按钮卡片开发经验.md`
- `Modules/DcatAdmin/DcatAdmin/Metrics/Base/ButtonCard-README.md`
- `ActionButtonMetric-README.md`
- `SingleButtonMetric-README.md`