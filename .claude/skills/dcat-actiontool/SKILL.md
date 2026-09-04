# Dcat Admin Action & Tool 开发技能

---
name: dcat-actiontool
description: 用于开发 Dcat Admin 的 Action 和 Tool 类。当用户提到"行操作"、"RowAction"、"工具栏按钮"、"Grid Tool"、"Show Tool"、"操作按钮"、"$grid->actions"、"$grid->tools"、"$show->tools"时触发。重点帮助实现正确的方式并遵循最佳实践。
---

## 技能概述

本技能用于开发符合项目规范的 Dcat Admin Action 和 Tool 类，包括：
- 区分 Action（行操作）和 Tool（工具栏按钮）
- 创建正确的 RowAction、Grid Tool、Show Tool 类
- 处理复杂的状态判断和业务逻辑
- 确保代码符合最佳实践

---

## ⚠️ 核心概念：Action vs Tool（必读）

### 目录结构规范

```
Modules/{模块}/DcatAdmin/
├── Actions/              # 行操作（RowAction）
│   ├── CopyAction.php
│   ├── DeleteAction.php
│   └── FixAction.php
└── Tools/                # 工具栏按钮（Tool）
    ├── SyncDataTool.php
    └── TestConnectionTool.php
```

### 继承关系速查表

| 场景 | 位置 | 继承类 | 命名空间 | 用途 |
|------|------|--------|----------|------|
| **Grid 列表行操作** | `$grid->actions()` | `RowAction` | `Modules\DcatAdmin\DcatAdmin\RowAction` | 每行数据的操作按钮 |
| **Grid 列表工具栏** | `$grid->tools()` | `Grid\Tools\AbstractTool` | `Dcat\Admin\Grid\Tools\AbstractTool` | 列表页顶部工具按钮 |
| **Show 详情页工具栏** | `$show->tools()` | `Show\AbstractTool` | `Dcat\Admin\Show\AbstractTool` | 详情页顶部工具按钮 |

### 三种基类的区别

#### 1. RowAction - 列表行操作

```php
// 位置：Actions/TestConnectionAction.php
namespace Modules\AFile\DcatAdmin\Actions;

use Modules\DcatAdmin\DcatAdmin\RowAction;

class TestConnectionAction extends RowAction
{
    protected $htmlClasses = ['action-primary'];
    
    public function title()
    {
        return '<i class="fa fa-plug"></i> 测试连接';
    }
    
    public function handle()
    {
        $id = $this->getKey(); // 自动获取当前行的 ID
        // ...
    }
}
```

**特点**：
- 自动获取当前行的 ID（`$this->getKey()`）
- 自动绑定到每一行数据
- 渲染时有 `$this->getRow()` 可访问行数据
- 执行时（handle）只有 `$this->getKey()`，需要自己查询数据库

#### 2. Grid\Tools\AbstractTool - 列表工具栏

```php
// 位置：Tools/SyncDataTool.php
namespace Modules\AFile\DcatAdmin\Tools;

use Dcat\Admin\Grid\Tools\AbstractTool;
use Illuminate\Http\Request;

class SyncDataTool extends AbstractTool
{
    protected $style = 'btn btn-primary';
    
    public function title()
    {
        return '同步数据';
    }
    
    public function handle(Request $request)
    {
        // 处理逻辑
        return $this->response()->success('同步成功')->refresh();
    }
}
```

**特点**：
- 显示在 Grid 列表顶部
- 不关联具体行数据
- 适合全局操作（同步、导出、批量处理）

#### 3. Show\AbstractTool - 详情页工具栏

```php
// 位置：Tools/TestConnectionTool.php
namespace Modules\AFile\DcatAdmin\Tools;

use Dcat\Admin\Show\AbstractTool;
use Dcat\Admin\Actions\Response;

class TestConnectionTool extends AbstractTool
{
    public function title()
    {
        return '<i class="fa fa-plug"></i> 测试连接';
    }
    
    public function handle(): Response
    {
        $id = $this->getKey(); // ✅ 自动获取详情页的 ID
        // ...
    }
}
```

**特点**：
- 显示在 Show 详情页顶部
- **自动获取详情页的 ID**（`$this->getKey()`）
- **不需要构造函数传递 ID**
- 默认样式：`btn btn-sm btn-primary`

### Controller 使用示例

```php
// Grid 列表页
protected function grid()
{
    return Grid::make(new Repository, function (Grid $grid) {
        // 行操作
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->append(new TestConnectionAction());  // RowAction
        });
        
        // 工具栏
        $grid->tools(new SyncDataTool());  // Grid\Tools\AbstractTool
    });
}

// Show 详情页
protected function detail($id)
{
    return Show::make($id, new Repository, function (Show $show) {
        // 工具栏
        $show->tools(function (Show\Tools $tools) {
            $tools->append(new TestConnectionTool());  // Show\AbstractTool
        });
    });
}
```

---

## ⚠️ 关键警告

### 普通 AJAX 按钮不要覆盖渲染方法

**普通 AJAX 按钮**（激活/停用/修复等）：
- ✅ 只实现 `title()` + `handle()` + `confirm()`
- ✅ 不覆盖 `render2()` 或 `html()`
- ✅ 让基类自动处理渲染和点击事件

```php
// ✅ 正确 - 普通 AJAX 按钮
class FixStorageConfigAction extends RowAction
{
    protected $htmlClasses = ['action-warning'];

    public function title()
    {
        return '<i class="fa fa-wrench"></i> 修复配置';
    }

    public function handle(): Response
    {
        // AJAX 处理逻辑
        return $this->response()->success('修复成功')->refresh();
    }

    // 不要覆盖 render2() 或 html()！
}
```

### 复杂交互可以覆盖 render2()

**复杂交互按钮**（Modal 弹窗/自定义表单）：
- ✅ 覆盖 `render2()` 方法
- ✅ 自己处理完整的交互逻辑
- ✅ 例如：Modal 弹窗、自定义表单

```php
// ✅ 正确 - 复杂交互（Modal 弹窗）
class EnterpriseResetPasswordAction extends RowAction
{
    protected $htmlClasses = ['action-warning'];
    protected $title = '重置密码';

    public function render2()
    {
        $form = EnterpriseResetPasswordForm::make()
            ->payload(['user_id' => $this->getKey()]);

        return Modal::make()
            ->title('重置密码')
            ->body($form)
            ->button('<i class="fa fa-key"></i> ' . $this->title);
    }
}
```

---

## RowAction 完整实现方式

### 方式1：普通 AJAX 按钮（推荐）

**适用场景**：
- ✅ 激活/停用
- ✅ 批准/拒绝
- ✅ 启用/禁用
- ✅ 修复配置
- ✅ 任何简单的状态变更操作

**必须实现的方法**：
- `title()` - 按钮标题
- `$htmlClasses` - 样式类（必须）
- `handle()` - AJAX 处理逻辑
- `confirm()` - 确认弹窗
- `allowed()` - 显示条件（可选）

**不要覆盖 render2()！**

**完整示例**：

```php
<?php

namespace Modules\Enterprise\DcatAdmin\Actions\Enterprise;

use Dcat\Admin\Actions\Response;
use Modules\DcatAdmin\DcatAdmin\RowAction;
use Modules\Enterprise\Models\EnterpriseEnterprise;
use Modules\Enterprise\Enums\EnterpriseAuthStatusEnum;
use Modules\Enterprise\Services\EnterpriseService;

/**
 * 批准企业认证操作
 *
 * 普通的 AJAX 按钮，不覆盖 render2()
 */
class ApproveAuthAction extends RowAction
{
    /**
     * 样式类（必须设置）
     */
    protected $htmlClasses = ['action-success'];

    /**
     * 按钮标题
     */
    public function title()
    {
        return '<i class="fa fa-thumbs-up"></i> 批准';
    }

    /**
     * 确认弹窗
     */
    public function confirm()
    {
        return '确定要批准此企业的认证吗？';
    }

    /**
     * 显示条件
     */
    public function allowed(): bool
    {
        $enterprise = EnterpriseEnterprise::find($this->getKey());

        if (!$enterprise) {
            return false;
        }

        $authStatus = $enterprise->auth_status instanceof EnterpriseAuthStatusEnum
            ? $enterprise->auth_status
            : EnterpriseAuthStatusEnum::tryFrom($enterprise->auth_status);

        if (!$authStatus) {
            return false;
        }

        return $authStatus->isPending();
    }

    /**
     * AJAX 处理
     */
    public function handle(): Response
    {
        try {
            EnterpriseService::approveAuthentication($this->getKey());

            return $this->response()
                ->success('认证批准成功')
                ->refresh();
        } catch (\Exception $e) {
            return $this->response()
                ->error($e->getMessage())
                ->refresh();
        }
    }
}
```

### 方式2：复杂交互（Modal 弹窗）

**适用场景**：
- ✅ 需要弹出表单
- ✅ 需要显示复选框、下拉框等复杂输入
- ✅ 需要自定义交互逻辑

**必须实现的方法**：
- `title()` - 按钮标题
- `render2()` - 渲染 Modal 或自定义 HTML
- `allowed()` - 显示条件

**需要自己处理完整交互逻辑！**

**完整示例1：Modal + 表单**

```php
<?php

namespace Modules\Enterprise\DcatAdmin\Actions\User;

use Modules\DcatAdmin\DcatAdmin\RowAction;
use Dcat\Admin\Widgets\Modal;
use Modules\Enterprise\DcatAdmin\Forms\EnterpriseResetPasswordForm;

/**
 * 重置企业用户密码操作
 *
 * 使用 Modal 弹窗显示表单，需要覆盖 render2()
 */
class EnterpriseResetPasswordAction extends RowAction
{
    protected $htmlClasses = ['action-warning'];
    protected $title = '重置密码';

    public function icon(): string
    {
        return 'fa fa-key';
    }

    /**
     * 渲染 Modal 弹窗
     */
    public function render2()
    {
        $userId = $this->getKey();

        $form = EnterpriseResetPasswordForm::make()
            ->payload([
                'user_id' => $userId,
            ]);

        return Modal::make()
            ->title('重置密码')
            ->body($form)
            ->button(
                '<i class="' . $this->icon() . '"></i> ' . $this->title
            );
    }
}
```

**对应的 Form 类**：

```php
<?php

namespace Modules\Enterprise\DcatAdmin\Forms;

use Dcat\Admin\Widgets\Form;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Contracts\LazyRenderable;

/**
 * 企业套餐续费表单
 */
class EnterpriseRenewTenantForm extends Form implements LazyRenderable
{
    use LazyWidget;

    public function handle(array $input)
    {
        $enterpriseId = $this->payload['enterprise_id'] ?? null;
        $planId = $input['plan_id'] ?? null;
        $remark = $input['remark'] ?? '';

        try {
            EnterpriseTenantPlanService::renewPlan($enterpriseId, $planId, $remark);
            return $this->response()->success('续费成功')->refresh();
        } catch (\Exception $e) {
            return $this->response()->error($e->getMessage());
        }
    }

    public function form()
    {
        $this->select('plan_id', '续费套餐')
            ->options($this->getPlanOptions())
            ->help('不选择则使用当前套餐续费');

        $this->textarea('remark', '备注')
            ->rows(3)
            ->maxlength(200)
            ->help('续费备注（可选）');
    }
}
```

---

## Tool 完整实现方式

### Grid\Tools\AbstractTool - 列表工具栏

```php
<?php

namespace Modules\AFile\DcatAdmin\Tools;

use Dcat\Admin\Grid\Tools\AbstractTool;
use Dcat\Admin\Actions\Response;
use Illuminate\Http\Request;
use Modules\AFile\Services\StorageConfigService;

/**
 * 同步文件系统配置工具
 */
class SyncFilesystemsTool extends AbstractTool
{
    /**
     * 按钮样式
     */
    protected $style = 'btn btn-primary';

    /**
     * 按钮文本
     */
    public function title()
    {
        return '同步public储存到数据库';
    }

    /**
     * 确认弹窗
     */
    public function confirm()
    {
        return [
            '确定要同步 filesystems.php 配置到数据库吗？',
            '这将读取 config/filesystems.php 中的磁盘配置并同步到数据库'
        ];
    }

    /**
     * 处理请求
     */
    public function handle(Request $request): Response
    {
        try {
            $result = StorageConfigService::syncFromFilesystems(
                app()->environment(),
                0
            );

            $message = sprintf(
                '同步成功！新增：%d，更新：%d，跳过：%d',
                $result['created'],
                $result['updated'],
                $result['skipped']
            );

            return $this->response()->success($message)->refresh();
        } catch (\Exception $e) {
            return $this->response()->error('同步失败：' . $e->getMessage());
        }
    }
}
```

### Show\AbstractTool - 详情页工具栏

```php
<?php

namespace Modules\AFile\DcatAdmin\Tools;

use Dcat\Admin\Actions\Response;
use Dcat\Admin\Show\AbstractTool;
use Modules\AFile\Services\StorageConfigService;

/**
 * 详情页测试存储连接 Tool
 *
 * 用于 Show 页面的 tools，继承 Show\AbstractTool
 */
class TestConnectionTool extends AbstractTool
{
    /**
     * Tool 标题
     */
    public function title()
    {
        return '<i class="fa fa-plug"></i> 测试连接';
    }

    /**
     * 确认对话框
     */
    public function confirm()
    {
        return ['确定要测试此存储连接吗？', '将尝试创建并删除测试文件'];
    }

    /**
     * 处理 Tool 请求
     */
    public function handle(): Response
    {
        $id = $this->getKey(); // ✅ 自动获取详情页的 ID

        if (!$id) {
            return $this->response()->error('缺少存储配置ID')->refresh();
        }

        // 使用静态方法调用
        $result = StorageConfigService::testConnectionById($id);

        if ($result['success']) {
            return $this->response()->success($result['message'])->refresh();
        }

        return $this->response()->error($result['message']);
    }
}
```

**关键特点**：
- ✅ 继承 `Show\AbstractTool`（不是 `Action`）
- ✅ 不需要构造函数传递 ID
- ✅ `$this->getKey()` 自动获取详情页的 ID
- ✅ 默认样式：`btn btn-sm btn-primary`

---

## RowAction 核心原则

### ⚠️ 重要：RowAction 的两种上下文（必读）

**渲染时 vs 执行时的数据访问差异**

#### 1. 渲染时（Grid 列表页面）

**调用方法**：`allowed()`、`title()`、`render()`、`render2()`

**上下文特点**：
- ✅ Grid 已经加载所有数据
- ✅ Action 实例包含完整的 row 数据
- ✅ 可以访问 `$this->getRow()` 和 `$this->getKey()`

**可以使用**：
```php
public function allowed(): bool
{
    $row = $this->getRow();  // ✅ 有完整数据

    if (!$row) {
        return false;
    }

    $config = $row->config;  // ✅ 可以访问属性
    $status = $row->status;  // ✅ 可以访问属性

    return $status === 'pending';
}

public function title()
{
    $row = $this->getRow();  // ✅ 有完整数据

    return '<i class="fa fa-check"></i> ' . $row->name;  // ✅ 可以访问属性
}
```

#### 2. 执行时（AJAX 请求）

**调用方法**：`handle()`

**上下文特点**：
- ⚠️ AJAX 请求，Action 重新实例化
- ⚠️ **只有 key，没有 row 数据**
- ❌ `$this->getRow()` 返回 null
- ✅ `$this->getKey()` 可用

**错误做法**：
```php
// ❌ 错误 - handle 中不能使用 getRow()
public function handle(): Response
{
    $row = $this->getRow();  // ❌ 返回 null！

    if (!$row) {
        return $this->response()->error('数据不存在');
    }

    $config = $row->config;  // ❌ 尝试访问 null 的属性会报错

    // 处理逻辑...
}
```

**正确做法**：
```php
// ✅ 正确 - handle 中必须自己查询数据库
public function handle(): Response
{
    $id = $this->getKey();  // ✅ 只能用 getKey()

    // ✅ 必须自己查询数据库
    $record = DB::table('table_name')->where('id', $id)->first();

    if (!$record) {
        return $this->response()->error('数据不存在或已被删除')->refresh();
    }

    $config = json_decode($record->config, true);  // ✅ 从数据库结果获取
    $status = $record->status;  // ✅ 从数据库结果获取

    // 处理逻辑...
}
```

#### 3. 对比总结表

| 方法 | 调用时机 | `$this->getRow()` | `$this->getKey()` | 数据来源 | 能否访问行属性 |
|------|---------|------------------|------------------|---------|-------------|
| `allowed()` | 渲染时 | ✅ 有完整数据 | ✅ 可用 | Grid 已加载 | ✅ 可以 |
| `title()` | 渲染时 | ✅ 有完整数据 | ✅ 可用 | Grid 已加载 | ✅ 可以 |
| `render2()` | 渲染时 | ✅ 有完整数据 | ✅ 可用 | Grid 已加载 | ✅ 可以 |
| `handle()` | AJAX 请求时 | ❌ 返回 null | ✅ 可用 | **需自己查询** | ❌ 不能 |

---

## 样式规范

**必须设置**：
```php
protected $htmlClasses = ['action-warning'];
```

**颜色映射**：
- `action-success` → `btn-outline-success`（绿色）
- `action-warning` → `btn-outline-warning`（黄色）
- `action-danger` → `btn-outline-danger`（红色）
- `action-info` → `btn-outline-primary`（蓝色）

---

## 常见错误

### ❌ 错误1：普通按钮覆盖 render2()

```php
// ❌ 错误 - 普通 AJAX 按钮不应覆盖 render2()
class FixStorageConfigAction extends RowAction
{
    public function render2()
    {
        return '<button>修复</button>';
    }
}
```

**问题**：破坏了 Dcat 的点击事件绑定！

**解决方案**：普通按钮不要覆盖 render2()

```php
// ✅ 正确
class FixStorageConfigAction extends RowAction
{
    protected $htmlClasses = ['action-warning'];

    public function title()
    {
        return '<i class="fa fa-wrench"></i> 修复';
    }

    public function handle(): Response
    {
        // AJAX 逻辑
    }
}
```

### ❌ 错误2：忘记设置 $htmlClasses

```php
// ❌ 错误
class MyAction extends RowAction
{
    public function title()
    {
        return '操作';
    }
}
```

**问题**：按钮样式不正确

**解决方案**：必须设置 $htmlClasses

```php
// ✅ 正确
class MyAction extends RowAction
{
    protected $htmlClasses = ['action-success'];

    public function title()
    {
        return '操作';
    }
}
```

### ❌ 错误3：AJAX 方式忘记 refresh()

```php
// ❌ 错误
public function handle(): Response
{
    Service::activate($this->getKey());
    return $this->response()->success('操作成功');
}
```

**问题**：Grid 不会刷新

**解决方案**：必须添加 refresh()

```php
// ✅ 正确
public function handle(): Response
{
    Service::activate($this->getKey());
    return $this->response()->success('操作成功')->refresh();
}
```

### ❌ 错误4：详情页 Tool 继承错误的基类

```php
// ❌ 错误 - 详情页 Tool 继承 Action
class TestConnectionDetailAction extends Action
{
    protected $configId;
    
    public function __construct(int $configId)
    {
        $this->configId = $configId;
    }
    
    public function handle()
    {
        $result = Service::test($this->configId);
        // ...
    }
}
```

**问题**：
- 不符合 Dcat Admin 架构
- 需要手动传递 ID
- 命名混乱（Action 但实际是 Tool）

**解决方案**：使用 Show\AbstractTool

```php
// ✅ 正确
class TestConnectionTool extends AbstractTool
{
    public function handle(): Response
    {
        $id = $this->getKey(); // ✅ 自动获取
        $result = Service::test($id);
        // ...
    }
}
```

---

## 参考示例

**RowAction（行操作）**：
- 普通 AJAX 按钮：`Modules/AFile/DcatAdmin/Actions/FixStorageConfigAction.php`
- Modal + Form：`Modules/Enterprise/DcatAdmin/Actions/Plan/EnterpriseRenewTenantAction.php`
- Modal + Form（重置密码）：`Modules/Enterprise/DcatAdmin/Actions/User/EnterpriseResetPasswordAction.php`

**Tool（工具栏按钮）**：
- Grid 工具栏：`Modules/AFile/DcatAdmin/Tools/SyncFilesystemsTool.php`
- Show 工具栏：`Modules/AFile/DcatAdmin/Tools/TestConnectionTool.php`
- 应用缓存刷新：`Modules/Application/DcatAdmin/Tools/RefreshCacheTool.php`

---

## 重要原则

**Dcat Admin 不手写 HTML**：
- ❌ 不要在 Action/Tool 中写 `<form>...</form>`
- ✅ 使用 Dcat Admin 的 Form 类构建表单
- ✅ Modal 弹窗配合 Form 类使用

**正确做法**：
```php
// ✅ 正确 - 使用 Dcat Form 类
public function render2()
{
    $form = EnterpriseRenewTenantForm::make()
        ->payload(['enterprise_id' => $this->getKey()]);

    return Modal::make()
        ->title('续费套餐')
        ->body($form)
        ->button($this->title());
}
```

**错误做法**：
```php
// ❌ 错误 - 手写 HTML
protected function buildFormHtml(): string
{
    return '<form action="...">
        <input type="text" name="plan_id">
        <textarea name="remark"></textarea>
    </form>';
}
```