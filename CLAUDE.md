This file provides guidance to Ai Agent when working with code in this repository.

---
始终使用中文回答,始终使用中文沟通
对复杂工作,拆分任务,逐一交由subagent执行

## 项目概述
项目名字: 个人工具（MyTool）

**项目定位**: 个人工具集合平台

**核心能力**:
- AI功能：智能AI辅助工具
- Excel处理：电子表格处理与数据分析
- 文件管理：便捷的文件存储与管理
- 模块化架构：基于 Laravel 12 的模块化设计

基于 Laravel 12 的模块化项目。

### 架构定位

**超管后台**: Dcat Admin 系统管理后台
- 路径: `/admin`
- 用户: 超级管理员
- 用途: 系统管理、配置
- 特点: 传统后台界面，完整 Grid/Form/Action 功能

### 模块化架构

```
核心模块层（基础设施，无业务逻辑）
├── ABase = 基础工具模块（ServiceProvider基类、Hook、Event、通用工具）
├── ApiProto = API框架（统一入口）
├── DcatAdmin = 后台框架集成
└── Debug = 调试工具

业务模块层（业务逻辑，允许互相依赖）
└── Application/Cms/Point/Notification/BackupAndClean/FeatureExcel/FeatureAi/AFile/China/Demo5

前台模块层（提供API/Web入口）
├── DcatAdmin Controllers → 超管后台Web界面
```

### 核心技术栈
- **PHP**: 8.4 | **Laravel**: 12
- **超管后台**: Dcat Admin 2.x (`/admin`)
- **模块**: nwidart/laravel-modules
- **验证库**: inhere/php-validate
- **消息队列**: php-amqplib (RabbitMQ)
- **Laravel扩展库**: DLaravel

### 本地环境
- 超管后台账号: admin / admin
- 日志驱动: daily(按天储存)
- 日志路径: `storage/logs/laravel-{date}.log`

---

## 项目架构

### 模块化开发核心原则

**单模块全栈架构**: 一个业务模块包含超管后台入口，完整业务逻辑，无跨模块依赖

**管理入口**:
- 超管后台：模块内 `DcatAdmin/Controllers/` 处理系统管理
- 共享业务逻辑：两个入口共享同一 Models/Services/Logics 层

**模块分层规范**:
- 核心模块（ABase,ApiProto,DcatAdmin）：只提供基础设施，禁止包含业务逻辑

**模块间通信**: 通过 Event 和 Hook 实现，禁止跨模块调用 Model/Service

**模块激活**: `modules_statuses.json` 控制模块启用/禁用

### 分层架构(单模块内部)

```
Models → Services → Logics → Controllers(模块内的DcatAdmin/Api入口)
```

| 层级 | 职责 | 关键规则 |
|------|------|---------|
| **Models** | 数据结构、关系映射 | Eloquent ORM，数据表使用模块名前缀 |
| **Services** | 协调组件、复杂业务 | **优先静态方法**，**禁止读取HTTP/session**，参数由Controller/Handler 显性传入 |
| **Logics** | 数据组装/单一逻辑 | **必须静态类**，不允许实例化，无状态 |
| **DcatAdmin Controllers** | 超管后台HTTP处理 | Grid/Form/Action，系统级管理 |
| **ApiProto Handlers** | API Handler | **禁止直接操作数据库**，需通过 Services 层 |

### ServiceProvider 继承链

```
业务模块 ServiceProvider → ABase\Support\ServiceProvider (抽象基类)
  - 自动处理: migrations, translations, views, config, commands, schedules

DcatAdmin路由 → RouteServiceProvider + DcatAdmin\Support\Traits\RouteServiceProviderTrait
  - 提供: mapAdminRoutes(), registerModuleRoutes()
```

### 模块间通信机制

**Hook 系统** (ABase 提供，同步调用):
- `HookManager::register(HookClass, callable)` — 注册监听器
- `HookManager::trigger(HookClass, Parameter)` — 触发 Hook
- 命令: `php artisan hook:list`、`php artisan hook:status`、`php artisan hook:test`

**Event 系统** (异步事件驱动):
- 模块通过 Events/Listeners 解耦
- 跨模块业务通过 Event 通信，禁止直接调用其他模块的 Model/Service

## 常用命令

### 模块管理
```bash
php artisan module:make {ModuleName}           # 创建模块
php artisan module:migrate --all              # 所有模块迁移
php artisan module:seed --all                  # 所有模块Seeder
php artisan module:migrate-status              # 查看迁移状态
```

### API 构建
```bash
composer proto                                 # 构建 API
```

### Dcat Admin
```bash
php artisan admin:sync-menu                    # 同步菜单
```

### 开发环境
```bash
composer run dev                               # 启动开发服务器(并发: serve+queue+pail+npm)
composer run cacheclear                        # 清理所有缓存
php artisan pail                               # 实时日志查看
```

### Api调试
```bash
# 请求旧站api,greenbid站点
php artisan legacy:api 

# 有request_id 可以重放请求,可以获取完整请求信息
php artisan debug:replay-request {request_id}  # 重放Api请求,优先使用request_id进行重放
php artisan debug:request-log --request_id={request_id}


php artisan enterprise:token [--enterprise-id=11] [--user-id=11] # 获取企业的可用测试token，默认企业11，企业ID与用户ID二选一

# 使用 Token 测试 Proto API
curl -H "Authorization: Bearer {token}" http://localhost:8000/api/proto/{module}/{handler}/{action}
```

### 测试
```bash
php artisan test                               # 运行测试
./vendor/bin/phpunit                           # PHPUnit
./vendor/bin/phpunit Modules/Enterprise/Tests  # 运行单个模块测试
./vendor/bin/phpunit --filter=TestName         # 运行单个测试
```

模块测试目录: `Modules/{模块}/Tests/Unit`、`Modules/{模块}/Tests/Feature`（phpunit.xml 已配置自动扫描）

### 代码格式化
```bash
./vendor/bin/pint                              # Laravel Pint 代码格式化(项目无pint.json,使用默认预设)
```

---

## 开发规范
REVIEW规范文件: REVIEW_RULE.md

### 必须遵守
- **模块化开发**: 所有代码在 `Modules/` 内，禁止修改 `app/`
- **PHPDoc规范**: 所有代码必须有注释
- **禁止try**: 非必要不要try，会掩盖错误
- **禁止构造函数属性提升语法**
- **避免定义'服务容器/依赖注入/Facades'**: 严重的过度设计
- **禁止执行数据库迁移**: 交由人工执行，禁止migrater
- **禁止使用 CDN 文件**
- **数据表前缀**: 每个模块的数据表使用模块名作为前缀
- **Proto Message 命名**: `{模块名}{功能}{Request/Response/Data}`（如 `EnterpriseDeptListRequest`）
- **pathlist.php 禁止手动编辑**: 由 `apiproto:generate-pathlist` 自动生成
- Handler的phpdocs声明具体的Message类名
- 优先使用ORM,而非DB

### inhere/php-validate 使用
- 编写独立的 Validation 类，配置验证规则
- 继承 `\Inhere\Validate\Validator\AbstractValidator` 实现自定义 Validator
- Validation: 验证类（如登录验证）
- Validator: 单个自定义验证规则（如手机号合规验证）

### 调试与验证
- **超管后台**: 使用 `playwright-laravel` agent 进行浏览器访问验证
- 使用 MCP `dbhub` 访问数据库
- ErrorException 在 Exception trace 中找真实错误位置
- 修改后用 `review-code` agent 进行代码review

---

## 技能系统(Skills)

项目包含专用技能，存放在 `.claude/skills/`

| Skill | 触发场景 | 适用范围 |
|-------|----------|----------|
| `dev-dcatadmin` | Dcat Admin模块开发(Grid/Form/Action) | 超管后台 |
| `dev-model` | Model开发、数据库关系映射 | 共享 |
| `dev-migration` | Migration开发、表结构设计 | 共享 |
| `dev-seeder` | Seeder开发、数据填充 | 共享 |
| `dev-blade` | Blade视图开发 | 超管后台 |
| `dev-hook` | Hook开发、模块通信 | 模块架构 |
| `php-validate` | php-validate库使用 | 共享 |
| `module-structure` | 模块架构指导、目录结构 | 共享 |
| `module-config-guide` | 模块配置指导 | 共享 |
| `admin-menu` | 后台菜单配置 | 超管后台 |
| `doc-manager` | 文档管理 | 共享 |
| `gitmrepo` | Git多仓库管理(mrepo非submodule) | 共享 |

---

## 文档存放规则(重要)

**项目文档(图书馆)** → `docs/`, `Modules/{模块}/docs`
- 模块设计文档、技术方案、文档API文档、数据库设计文档
- 长期使用的团队文档,不要主动往里放东西
- 命名: `{中文主题}.md` 

** Ai工作追踪文档(工作台)** → `AiWork/`
- 审查报告、开发记录、问题排查(在`AiWork/{YYYYMM}/{DD}/`(带年月/日的子目录)
- 统一任务必须单文档维护: 全过程在同一文档记录，变更状态
- 命名: `{年月}/{日}/{日-时分-主题}.md` (如 `202605/30/30-0934-Novel审查报告.md`)

**工作日志(日志)** → `AiWork/{YYYYMM}/{DD}-log/`
- 日常工作日志、决策记录、临时事项
- 命名: `{年月}/{日}-log/{日-时分-主题}.md` (如 `202605/01-log/01-1030-工作日志.md`)

**进度总览** → `AiWork/Work.md`
- 工作进度总览，任务追踪，要维护
- 固定文件名: `Work.md`
