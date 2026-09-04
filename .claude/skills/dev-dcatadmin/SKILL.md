---
name: dev-dcatadmin
description: 进行Dcat Admin的后台开发,阅读/处理/分析Dcat相关文件时必须使用
---

# Dcat Admin Development

## 说明

此技能专用于基于 Laravel 12 + Dcat Admin 的后台管理系统开发。项目采用模块化架构，集成 nwidart/laravel-modules 模块系统，专注于构建高效、可维护的管理后台。

### 技术栈


- **Laravel Framework**: 12
- **Dcat Admin**: dcat2 (dongasai/dcat-admin2)
- **模块系统**: nwidart/laravel-modules
- **数据库**: MySQL 8.0+
- **前端**: Bootstrap + jQuery

### 项目架构特点

- **模块化设计**: 使用 Laravel Modules 进行功能模块化
- **分层架构**: Services → Logics → Repositories ( 仅用于 Dcat Admin) → Models 
- **事件驱动**: 使用事件系统解耦组件
- **标准化目录**: 遵循 Demo5 模块标准结构

## 核心概念，参考：Modules/Demo5Admin模块
- 最佳实践: Modules/Demo5Admin模块 

### 1. 模块结构

项目采用独有的 Laravel 模块结构：

```
Modules/{ModuleName}/
├── DcatAdmin/                     # 后台管理
│   ├── Controllers/          # 后台控制器
│   ├── Requests/             # 表单验证
│   ├── Actions/              # 行为操作（RowAction）
│   ├── Tools/                # 工具栏按钮（Tool）
│   ├── Metrics/              # 图表
│   └── Repositories/         # Dcat Admin 数据访问工具（后台专用）
├── Models/                   # Eloquent模型
├── Services/                 # 服务层
├── Logics/                   # 业务逻辑层
├── routes/                   # 路由定义
│   ├── admin.php            # 后台路由
│   ├── api.php              # API路由
│   └── web.php              # Web路由
└── config/                   # 配置文件
```

### 2. 控制器层次

- **AdminController**: Dcat Admin 基础控制器
- **Content**: 页面内容构建器
- **Grid**: 数据表格展示
- **Form**: 表单构建器
- **Show**: 详情展示器

### 3. 架构层次说明

**核心业务层：**
- **Models**: Eloquent 模型，数据结构和业务方法
- **Services**: 服务层，协调复杂业务流程
- **Logics**: 逻辑层，纯函数计算业务逻辑

**Dcat Admin 后台层：**
- **Admin/Controllers**: 后台控制器，处理HTTP请求
- **Admin/Repositories**: Dcat Admin 数据访问工具，提供Grid/Filter支持（仅后台使用）
- **Admin/Actions**: 行为操作，处理单条和批量操作
- **Admin/Requests**: 表单验证，请求数据验证

## 最佳实践

### 开发流程
- 先创建Admin/Repositories → 路由 → 控制器 → Actions/Tools/图表

### Action vs Tool 规范（重要）

**目录结构**：
- `Actions/` - 存放行操作（RowAction）
- `Tools/` - 存放工具栏按钮（Tool）

**继承关系速查表**：

| 场景 | 位置 | 继承类 | 命名空间 | 用途 |
|------|------|--------|----------|------|
| **Grid 列表行操作** | `$grid->actions()` | `RowAction` | `Modules\DcatAdmin\DcatAdmin\RowAction` | 每行数据的操作按钮 |
| **Grid 列表工具栏** | `$grid->tools()` | `Grid\Tools\AbstractTool` | `Dcat\Admin\Grid\Tools\AbstractTool` | 列表页顶部工具按钮 |
| **Show 详情页工具栏** | `$show->tools()` | `Show\AbstractTool` | `Dcat\Admin\Show\AbstractTool` | 详情页顶部工具按钮 |

**关键原则**：
- ✅ 普通 AJAX 按钮不覆盖 `render2()` 或 `html()`
- ✅ 只实现 `title()` + `handle()` + `confirm()`
- ✅ 必须设置 `$htmlClasses` 样式属性
- ✅ `handle()` 方法中必须自己查询数据库（AJAX 重新实例化，`getRow()` 返回 null）
- ✅ Show\AbstractTool 不需要构造函数传递 ID，`$this->getKey()` 自动获取

**参考技能**：详细实现请参考 `.claude/skills/dcat-actiontool/SKILL.md`

### 数据表格（Grid）
- 表格行操作用 RowAction
- 多行(批量)操作用 BatchActions
- 工具栏按钮用 Grid\Tools\AbstractTool

### 数据仓库（Repository）
- Dcat Admin 模型要有数据访问组件
- 有模型的Admin/Repository要简单,有eloquentClass属性即可

### 图表开发
- Metrics的方式实现图表 @./dcat2/widgets-charts.md

### 其他规范
- 兼容PC浏览器,不需要移动端浏览器兼容
- dcat开发不需要css/js
- 遇到问题看Demo5的最佳实践和Dcat源代码来解决
## 常见问题
`Route [dcat.admin.workflow.modules.overview] not defined.`这种就是路由前缀的问题,`php artisan route:list`一看便知,注意谨慎命令筛选输出,会掩盖错误

## 实际案例参考

### RowAction（行操作）
- **普通 AJAX 按钮**：`Modules/AFile/DcatAdmin/Actions/TestConnectionAction.php`
- **Modal + Form**：`Modules/Enterprise/DcatAdmin/Actions/Plan/EnterpriseRenewTenantAction.php`
- **修复配置**：`Modules/AFile/DcatAdmin/Actions/FixStorageConfigAction.php`

### Tool（工具栏按钮）
- **Grid 工具栏**：`Modules/AFile/DcatAdmin/Tools/SyncFilesystemsTool.php`
- **Show 工具栏**：`Modules/AFile/DcatAdmin/Tools/TestConnectionTool.php`
- **缓存刷新**：`Modules/Application/DcatAdmin/Tools/RefreshCacheTool.php`

## 文档列表
- [常见问题](./dcat2/qa.md)
- ## 数据表格
  - [基本使用](./dcat2/model-grid.md)
  - [列的使用和扩展](./dcat2/model-grid-column.md)
  - [列的显示和扩展](./dcat2/model-grid-column-display.md)
  - [行的使用和扩展](./dcat2/model-grid-actions.md)
  - [工具栏](./dcat2/model-grid-custom-tools.md)
  - **Action vs Tool 规范**：参考 `.claude/skills/dcat-actiontool/SKILL.md`
  - [树状表格](./dcat2/model-grid-tree.md)
  - [组合表头](./dcat2/model-grid-combination.md)
  - [数据源](./dcat2/model-grid-data.md)
  - [关联关系](./dcat2/model-grid-relationship.md)
  - [查询过滤](./dcat2/model-grid-filters.md)
  - [列过滤器](./dcat2/model-grid-column-filter.md)
  - [快捷搜索](./dcat2/model-grid-quick-search.md)
  - [规格筛选器](./dcat2/model-grid-selector.md)
  - [数据导出](./dcat2/model-grid-export.md)
  - [快捷创建](./dcat2/model-grid-quick-create.md)
  - [行内编辑](./dcat2/model-grid-editable.md)
  - [事件](./dcat2/model-grid-events.md)
  - [字段翻译](./dcat2/model-grid-trans.md)
  - [头部和脚部](./dcat2/model-grid-header.md)
  - [软删除](./dcat2/model-grid-softdelete.md)
  - [异步渲染](./dcat2/model-grid-softdelete.md)
- ## 数据表单
  - [基本使用](./dcat2/model-form.md)
  - [图片/文件上传](./dcat2/model-form-upload.md)
  - [字段使用](./dcat2/model-form-fields.md)
  - [字段扩展](./dcat2/model-form-field-management.md)
  - [数据源](./dcat2/model-form-data.md)
  - [表单弹窗](./dcat2/model-form-modal.md)
  - [关联关系](./dcat2/model-relationship.md)
  - [JSON表单](./dcat2/model-json.md)
  - [字段动态显示](./dcat2/model-form-when.md)
  - [表单分步](./dcat2/model-form-step.md)
  - [表单验证](./dcat2/model-form-validation.md)
  - [事件](./dcat2/model-form-callback.md)
  - [表单初始化](./dcat2/model-form-init.md)
  - [工具表单](./dcat2/widgets-form.md)
  - [表单布局](./dcat2/model-form-layout.md)
  - [表单字段翻译](./dcat2/model-form-trans.md)
- ## 数据详情
  - [基本使用](./dcat2/model-show.md)
  - [字段显示](./dcat2/model-show-field.md)
  - [关联关系](./dcat2/model-show-relation.md)
  - [显示扩展](./dcat2/model-show-extend.md)
  - [初始化](./dcat2/model-show-init.md)
  - [字段翻译](./dcat2/model-show-trans.md)
  - **Show Tool 规范**：参考 `.claude/skills/dcat-actiontool/SKILL.md`
- ## [模型树](./dcat2/model-tree.md)
- ## [数据仓库](./dcat2/model-repository.md)
- ## 动作
  - [基本使用](./dcat2/action.md)
  - [数据表格](./dcat2/action-grid.md)
  - [数据表单](./dcat2/action-form.md)
  - [数据详情](./dcat2/action-show.md)
  - [模型树](./dcat2/action-tree.md)
  - **详细规范**：参考 `.claude/skills/dcat-actiontool/SKILL.md`
- ## [多语言](./dcat2/trans.md)
- ## 扩展
  - [扩展基本使用](./dcat2/extension-f.md)
  - [开发扩展](./dcat2/extension-dev.md)
  - [开发主题](./dcat2/extension-theme.md)
- ## 页面组件
  - [异步加载](./dcat2/lazy.md)
  - [图表](./dcat2/widgets-charts.md)
  - [数据统计卡片](./dcat2/widgets-data-card.md)
  - [工具表单](./dcat2/widgets-form.md)
  - [模态窗(Modal)](./dcat2/widgets-modal.md)
  - [下拉菜单](./dcat2/widgets-dropdown.md)
  - [单/复选框](./dcat2/widgets-checkbox.md)
  - [选项卡](./dcat2/widgets-tab.md)
  - [告警框](./dcat2/widgets-alert.md)
  - [提示窗(tooltip)](./dcat2/widgets-tooltip.md)
  - [Markdown](./dcat2/widgets-markdown.md)
  - [卡片](./dcat2/widgets-box.md)
- ## [区块(section)](./dcat2/section.md)
- ## [动作以及表单响应](./dcat2/response.md)
- ## [权限控制](./dcat2/permission.md)
- ## [菜单](./dcat2/menu.md)
- ## [帮助函数](./dcat2/function.md)
- ## [开发工具](./dcat2/helpers.md)
- ## [自定义登陆认证](./dcat2/custom-authentication.md)
- ## [自定义头部导航](./dcat2/custom-navbar.md)

> 要学会查文档,搜索dcat2目录的文档找到所需内容
