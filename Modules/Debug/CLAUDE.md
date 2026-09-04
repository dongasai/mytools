# Debug 模块

开发调试工具模块，通过 Web 界面提供调试功能，仅用于开发环境。

---

## 模块定位

**架构层级**: 工具/功能模块

**环境限制**: 仅开发环境使用，生产环境应禁用

**数据表前缀**: 无（无数据库迁移）

**入口形式**: Web（路由前缀 `/debug`）+ Commands

**页面架构**: 外壳页面（导航+iframe）+ 内容页面（必须嵌入）

---

## 核心功能

### 已实现

| 功能 | 入口 | 说明 |
|------|------|------|
| 主外壳页面 | `GET /debug` | 包含导航栏和 iframe 容器的单页面应用框架 |
| 首页内容 | `GET /debug#index` | 工具列表导航（仅 iframe 嵌入） |
| 路由列表 | `GET /debug#routes` | 查看所有已注册路由，支持前缀过滤、关键字过滤、分组显示 |
| 服务器信息 | `GET /debug#server` | 显示服务器环境变量和配置信息 |
| ApiProto测试 | `GET /debug#api-proto` | ApiProto接口调试工具，支持参数配置和请求发送 |
| 请求重放 | `php artisan debug:replay-request {unid}` | 根据 unid 读取请求日志并重放 |

### 待开发

- Artisan 命令: LogCommand, StatusCommand, CacheCommand
- Web 页面: 日志查看、系统状态、缓存调试

---

## 目录结构

```
Debug/
├── Commands/                   # Artisan 命令
├── Http/Controllers/           # Web 控制器
├── routes/web.php              # Web 路由
├── resources/views/            # 视图文件
├── resources/assets/           # 静态资源
└── config/config.php           # 模块配置
```

无 Models/Services/Logics 层，无数据库迁移。

---

## 路由

所有路由前缀 `/debug`，访问 `/debug` 进入调试工具主页。

导航通过 URL hash 切换：`#routes`、`#server`、`#api-proto`

---

## 页面架构

**设计模式**: 外壳页面（`/debug`）+ 内容页面（iframe 嵌入）

**使用方式**:
- 访问 `/debug` 进入调试工具主页
- 通过顶部导航切换不同工具
- 使用 URL hash 直接定位：`/debug#routes`、`/debug#server` 等

**开发约束**:
- 内容页面必须使用 `<x-debug::layouts.content>` 布局组件
- 内容页面会自动检测 iframe，直接访问时跳转到外壳页面

---

## 添加新调试功能

### 开发步骤

1. 创建控制器方法（无 Models/Services 层）
2. 创建视图：使用 `<x-debug::layouts.content>` 布局
3. 注册路由
4. 在外壳页面导航栏添加链接

### 加载 JS/CSS

**方式一：模块资源发布**

```bash
# 发布所有模块资源到 public/modules/
php artisan module:publish-assets

# 或仅发布 Debug 模块
php artisan module:publish-assets Debug
```

资源发布后，在 Blade 视图中引用：

```blade
<!-- CSS -->
<link href="/modules/debug/css/your-style.css" rel="stylesheet">

<!-- JS -->
<script src="/modules/debug/js/your-script.js"></script>
```

**方式二：直接引入 CDN（禁止）**

项目禁止使用 CDN 文件，必须使用本地资源。

### 静态资源管理

**存放位置**: `resources/assets/`

```
resources/assets/
├── css/
│   ├── bootstrap.min.css
│   └── frame.css
└── js/
    ├── jquery.min.js
    └── bootstrap.min.js
```

**发布机制**:
- 使用 Laravel 模块资源发布命令
- 资源会从 `resources/assets/` 复制到 `public/modules/debug/`
- 访问路径：`/modules/debug/{relative-path}`

**支持的文件类型**: CSS、JS、图片、字体等常见静态资源

---

## 开发规范

- **禁止在生产环境启用**：本模块为调试工具，生产环境必须禁用
- **无数据库操作**：无 Models/Services 层，控制器直接处理
- **页面布局**：内容页面使用 `<x-debug::layouts.content>` 组件

## 调试工具使用

### 路由列表工具（`/debug#routes`）

查看所有已注册路由，支持：
- 前缀过滤（如 `api`, `admin`）
- 关键字搜索
- 分组显示

### 服务器信息工具（`/debug#server`）

显示服务器环境变量和 PHP 配置信息。

### ApiProto 测试工具（`/debug#api-proto`）

调试 Proto API 接口：
- 选择 Handler 和 Action
- 配置请求参数（JSON 格式）
- 发送测试请求
- 查看响应结果

### 请求重放命令

```bash
php artisan debug:replay-request {unid}
```

根据 unid 重放已记录的 API 请求（日志路径：`storage/logs/requests/{unid}.json`）

---


---

**更新时间**: 2026-07-12
**维护者**: AI 开发团队