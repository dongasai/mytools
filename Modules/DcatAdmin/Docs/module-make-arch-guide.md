# 模块创建命令使用指南

## 命令说明

`module:make-arch` 命令用于创建符合项目架构规范的 Laravel 模块。

## 工作原理

命令基于 **Emptyarch 模板模块** 复制方式：

1. 复制 `Modules/Emptyarch` 模板模块到新模块
2. 替换所有命名空间、类名、变量名
3. 更新 module.json 配置
4. 生成完整可用的新模块

## 使用方法

### 基本用法

```bash
php artisan module:make-arch {ModuleName}
```

### 示例

```bash
# 创建 NnnAgent 模块
php artisan module:make-arch NnnAgent

# 创建 TestModule 模块
php artisan module:make-arch TestModule
```

### 可选参数

- `--priority=N`: 设置模块优先级（默认：20）
- `--type=TYPE`: 设置模块类型（默认：feature）

```bash
# 创建高优先级核心模块
php artisan module:make-arch CoreModule --priority=10 --type=base

# 创建工具模块
php artisan module:make-arch HelperModule --priority=30 --type=tool
```

## 创建的目录结构

```
Modules/{ModuleName}/
├── DcatAdmin/              # 后台管理
│   ├── Controllers/        # 控制器
│   ├── Actions/            # 行为操作
│   ├── Forms/              # 表单
│   ├── Metrics/            # 图表
│   ├── Repositories/       # 数据仓库
│   ├── Requests/           # 表单验证
│   └── Tools/              # 工具栏按钮
├── Models/                 # Eloquent模型
├── Services/               # 服务层
├── Logics/                 # 业务逻辑层
├── Providers/              # 服务提供者
│   ├── {ModuleName}ServiceProvider.php
│   ├── RouteServiceProvider.php
│   └── EventServiceProvider.php
├── Hooks/                  # 钩子系统
│   ├── Definitions/
│   ├── Parameters/
│   └── Results/
├── routes/                 # 路由定义
│   ├── admin.php          # 后台路由
│   ├── api.php            # API路由
│   └── web.php            # Web路由
├── Database/              # 数据库相关
│   ├── Migrations/
│   ├── Factories/
│   └── Seeders/
├── Events/                # 事件定义
├── Listeners/             # 事件监听器
├── Enums/                 # 枚举类型
├── Dtos/                  # 数据传输对象
├── Validations/           # 验证类
├── QueueJobs/             # 队列任务
├── Commands/              # 命令
├── Tests/                 # 测试文件
├── Docs/                  # 文档目录
├── config/                # 配置文件
├── module.json            # 模块配置
└── README.md              # 模块说明
```

## 创建的文件

### 1. DashboardController

示例控制器，包含基本的仪表盘页面。

### 2. ServiceProvider

继承自 `Modules\ABase\Support\ServiceProvider`，包含：
- 模块名称定义
- boot() 方法
- register() 方法
- registerCommands() 方法

### 3. Hook 系统

完整的钩子系统示例：
- `{ModuleName}Hook`: 钩子定义
- `{ModuleName}Parameter`: 钩子参数
- `{ModuleName}Result`: 钩子结果

### 4. 路由文件

- `routes/admin.php`: 后台路由（已包含仪表盘路由）
- `routes/api.php`: API 路由模板
- `routes/web.php`: Web 路由模板

### 5. module.json

模块配置文件，包含：
- 模块名称和别名
- 描述和关键词
- 优先级和类型
- ServiceProvider 注册
- 模块依赖关系

### 6. README.md

模块说明文档模板。

## 模板模块

### Emptyarch 模块

**位置**: `Modules/Emptyarch/`

**用途**: 作为创建新模块的模板

**特点**:
- 包含完整的目录结构
- 包含示例文件
- 已禁用（不参与系统运行）
- 可随时查看和修改

### 修改模板

如需调整生成的模块内容，直接修改 `Modules/Emptyarch` 模块：

```bash
# 修改模板的 ServiceProvider
vim Modules/Emptyarch/Providers/EmptyarchServiceProvider.php

# 添加更多示例文件
touch Modules/Emptyarch/Services/ExampleService.php
```

修改后，新创建的模块会自动包含这些改动。

## 下一步操作

模块创建后，需要：

### 1. 完善 README

```bash
vim Modules/{ModuleName}/README.md
```

补充：
- 模块定位说明
- 核心功能列表
- 其他文档

### 2. 创建数据库迁移

```bash
# 手动创建迁移文件
# 参考：Modules/Demo5/Database/Migrations/
```

### 3. 创建 Models 层

```bash
# 手动创建模型
# 参考：Modules/Demo5/Models/
```

### 4. 创建 Services 和 Logics 层

```bash
# 手动创建服务层和逻辑层
# 参考：Modules/Demo5/Services/
# 参考：Modules/Demo5/Logics/
```

### 5. 开发 DcatAdmin 后台

```bash
# 创建 Controllers、Repositories、Actions 等
# 参考：Modules/Demo5/DcatAdmin/
```

## 对比 module:make

| 特性 | module:make | module:make-arch |
|------|-------------|------------------|
| ServiceProvider 继承 | ❌ Nwidart\Modules\Support\ModuleServiceProvider | ✅ Modules\ABase\Support\ServiceProvider |
| 目录结构 | ❌ 标准 Laravel Modules | ✅ 项目规范结构 |
| 无用文件 | ❌ 生成 vite、package 等 | ✅ 不生成 |
| 符合项目架构 | ❌ 需要手动调整 | ✅ 完全符合 |
| 模板可视化 | ❌ 不可能 | ✅ 可查看 Emptyarch |
| 维护性 | ❌ 需修改命令代码 | ✅ 修改模板即可 |

## 最佳实践

1. **使用新命令**: 始终使用 `module:make-arch` 创建新模块
2. **参考 Demo5**: 开发时参考 Demo5 模块的最佳实践
3. **遵循架构**: 严格遵守 Models → Services → Logics → Controllers 分层
4. **完善文档**: 及时更新 README.md 和相关文档
5. **修改模板**: 如需调整，修改 Emptyarch 模板

## 常见问题

### Q: 如何添加更多模板内容？

A: 直接修改 `Modules/Emptyarch` 模块，添加你需要的文件。例如：

```bash
# 添加示例 Service
cat > Modules/Emptyarch/Services/ExampleService.php << 'EOF'
<?php
namespace Modules\Emptyarch\Services;

class ExampleService
{
    // 示例方法
}
EOF
```

下次创建模块时会自动包含这个文件。

### Q: 能否创建不同类型的模块？

A: 可以使用 `--type` 参数：

```bash
php artisan module:make-arch ApiModule --type=api
php artisan module:make-arch ToolModule --type=tool
```

也可以创建多个模板模块：
- `EmptyarchApi`: API 模块模板
- `EmptyarchTool`: 工具模块模板

### Q: 模块没有被识别怎么办？

A: 运行以下命令：

```bash
composer dump-autoload
php artisan module:list
```

---

**创建时间**: 2026-09-01
**更新时间**: 2026-09-01
**维护者**: AI 开发团队