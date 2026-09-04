# 个人工具 (MyTool)

> 个人工具集合平台，提供多种实用工具功能

## 项目定位

个人工具集合，整合多种实用工具功能，包括AI辅助、Excel处理、文件管理等，为个人用户提供便捷的工具服务。

## 核心功能

- **AI功能**: 智能AI辅助工具
- **Excel处理**: 电子表格处理与数据分析
- **文件管理**: 便捷的文件存储与管理
- **演示模块**: 功能演示与测试
- **调试工具**: 开发调试辅助

## 技术栈

- **后端**: Laravel 12+ / PHP 8.4
- **模块化**: nwidart/laravel-modules
- **管理后台**: Dcat Admin
- **API架构**: Proto API（自定义 Protobuf 协议）
- **数据库**: MySQL 8.0+
- **缓存**: Redis

## 架构特点

**模块化设计**:
- 核心模块层（基础设施）
- 业务模块层（功能模块）
- 前台模块层（API/Web入口）

**管理后台**:
- 超管后台：Dcat Admin 系统管理后台 (/admin)

## 快速开始

```bash
# 安装依赖
composer install

# 配置环境
cp .env.example .env
php artisan key:generate

# 数据库迁移
php artisan migrate
php artisan module:migrate --all

# 安装后台
php artisan admin:install

# 启动服务
composer run dev
```

## 访问地址

- 后台管理: http://localhost:8000/admin
- 默认账号: admin / admin

## 模块列表

| 模块 | 说明 |
|------|------|
| ABase | 基础框架模块 |
| ApiProto | API协议框架 |
| DcatAdmin | 后台管理框架 |
| FeatureAi | AI功能模块 |
| FeatureExcel | Excel处理模块 |
| AFile | 文件管理模块 |
| Application | 应用基础模块 |
| Demo5 | 演示模块 |
| Debug | 调试工具模块 |
| China | 中国数据模块 |

## 开发规范

- 模块化开发，所有代码在 `Modules/` 内
- PHPDoc规范注释
- Service层优先静态方法
- 禁止跨模块直接调用 Model/Service

## 文档

- 项目文档: `docs/`
- AI工作追踪: `AiWork/`
