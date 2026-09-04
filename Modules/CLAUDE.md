# 模块架构文档

本文档梳理 AI 辅助开发平台"三牛（CodeNnn）"的所有模块，按照架构分层组织。

---

## 项目定位

**三牛（CodeNnn: The Next‑gen Neural Nexus for Software Creation）**

**核心能力**:
- AI 代码生成：基于大语言模型的智能代码生成
- 智能重构：代码分析与优化建议
- 自动化测试：智能测试用例生成与执行
- 模块化架构：基于 Laravel 12 的三层架构设计

---

## 架构分层总览

```
核心模块层（基础设施，无业务逻辑）
└── ABase = 基础工具模块（ServiceProvider基类、Hook、Event、通用工具）

业务模块层（核心业务逻辑）
├── NnnnMain = 主模块（核心业务协调）
├── NnnAgent = AI Agent 模块（AI Agent 管理与执行）
└── NnnProject = 项目管理模块（项目资源管理）

功能模块层（通用功能支持）
├── FeatureAi = AI 功能模块（AI 能力集成）
├── FeatureExcel = Excel 导入导出引擎（模板类驱动）
├── FeatureExcelDemo = FeatureExcel 演示模块（集成示例）
├── FeatureSms = 短信功能模块（验证码、短信网关）
├── FeatureSsh = SSH 功能模块（SSH 服务器管理与远程操作）
├── AFile = 文件管理模块（文件/图片上传、存储、访问）
├── Notification = 消息通知模块（统一通知系统）
├── ASync = 数据同步模块（数据库变更同步）
├── Cms = 内容管理模块（文章、分类管理）
├── China = 中国区数据模块（中国特殊内容）
└── Application = 应用通用模块（应用通用定义）

前台模块层（提供 API/Web 入口）
└── DcatAdmin = 超管后台 Web 界面

模板模块（不参与业务）
└── Emptyarch = 空架构模板（创建新模块的模板）

调试工具
└── Debug = 调试模块（日志分析、状态监控）

演示模块
└── Demo5 = 演示模块（模块化最佳实践演示）
```

---

## 一、业务模块层

### NnnnMain - 主模块

**模块定位**: 核心业务协调模块

**核心功能**: 核心业务协调、跨模块协调服务

---

### NnnAgent - AI Agent 模块

**模块定位**: AI Agent 管理与执行模块

**核心功能**:
- AI Agent 管理
- Agent 执行引擎
- 钩子系统集成
- 参数与结果管理

**数据表前缀**: `nnnagent_`

**依赖**: NnnProject（在项目上执行任务）

---

### NnnProject - 项目管理模块

**模块定位**: 项目资源管理模块

**核心功能**:
- 项目生命周期管理（创建、编辑、删除）
- 本地项目管理
- SSH远程项目管理
- 项目类型识别
- 项目配置管理

**数据表前缀**: `nnnproject_`

**依赖**: FeatureSsh（SSH项目需要SSH连接）

---

## 二、核心模块层

### ABase - 基础工具模块

**模块定位**: 核心基础模块，为其他模块提供基础功能、基类、工具类和服务。优先级最高、不包含业务逻辑、所有模块的依赖基础

**核心功能**:
- 配置表备份服务
- 枚举类型定义
- 工具类（配置表枚举辅助工具）
- Console 命令（项目树生成、配置备份、模型注释生成）
- 基础 ServiceProvider

---

## 三、功能模块层

### FeatureAi - AI 功能模块

**模块定位**: AI 能力集成模块

**核心功能**: AI 相关功能集成、LLM API 调用、AI 能力封装

---

### FeatureExcel - Excel 导入导出引擎模块

**模块定位**: Excel/CSV 导入导出引擎，提供模板类驱动的数据导入导出能力

**核心功能**:
- 模板类驱动的字段映射定义
- 双引擎支持（Excel/CSV 自动识别）
- 验证引擎（inhere/php-validate 集成）
- 大数据处理（超过 5000 行自动分块）
- 导出优化（数据指纹缓存）
- 安全机制（路径验证、数据验证、AFile 集成）

**对外服务**: ModuleFeatureExcelService（静态方法）

**数据表前缀**: 无独立数据表

---

### FeatureExcelDemo - FeatureExcel 演示模块

**模块定位**: 演示模块，完整展示 FeatureExcel 模块集成方式

**核心功能**: 订单导入导出完整示例、Service 层业务逻辑

**演示特性**:
- ✅ 流式字段映射构建
- ✅ 业务验证钩子（validateRow）
- ✅ 数据转换钩子（transformRow）
- ✅ 数据格式化钩子（formatRow）
- ✅ Service 层业务逻辑

**数据表前缀**: `demo_orders`

**集成参考**: 其他业务模块集成 FeatureExcel 时，参考 FeatureExcelDemo 的完整实现

---

### FeatureSms - 短信功能模块

**模块定位**: 短信验证码发送、验证等功能模块

**核心功能**:
- 短信验证码发送
- 短信验证码验证
- 多种验证码类型支持（登录、注册、密码重置）
- 短信网关配置管理
- 验证码有效期控制
- 防重复发送机制
- Admin 后台管理界面

**数据表前缀**: `fsms_`

---

### FeatureSsh - SSH 功能模块

**模块定位**: SSH 服务器管理与远程操作功能模块

**核心功能**:
- SSH 服务器管理（服务器信息、分组、状态监控）
- SSH 认证管理（密钥对、密码、证书、Agent）
- SSH 连接管理（连接池、会话管理、断线重连）
- SSH Bash 执行（命令执行、实时输出、后台任务）
- SFTP 文件传输（上传下载、目录操作、权限管理）

**数据表前缀**: `fssh_`

**被依赖模块**: NnnAgent（远程项目）、ASync（远程同步）

---

### AFile - 文件管理模块

**模块定位**: 文件和图片上传、存储、访问的基础模块，供其他模块使用

**核心功能**:
- 文件上传、存储、访问
- 图片上传、存储、访问
- 公共和私有文件区分
- 文件与其他模块实体关联
- 文件模板管理

**数据表前缀**: `afile_`

---

### Notification - 消息通知模块

**模块定位**: 统一消息通知系统，整合短信、邮件、推送等多个通知渠道

**核心功能**: 统一通知接口、多渠道通知整合、通知发送管理

**数据表前缀**: `notification_`

---

### ASync - 数据同步模块

**模块定位**: 数据库变更同步模块，用于向正式服/预发布服务器同步数据库变更

**核心功能**:
- 数据库连接管理
- 同步计划管理
- 表同步配置
- 预览 SQL 机制
- 任务执行与监控
- 回滚恢复

**数据表前缀**: `sync_`

---

### Cms - 内容管理模块

**模块定位**: 内容管理系统，采用单模块全栈架构

**核心功能**:
- 文章管理（创建、编辑、删除）
- 分类管理（层级分类系统）
- 内容发布（富文本编辑）
- 事件系统（文章创建、更新、查看事件）
- 完整的 CRUD 操作

**数据表前缀**: `cms_`

---

### China - 中国区数据模块

**模块定位**: 中国特殊内容模块

**核心功能**: 中国区数据定义、特殊内容处理

---

### Application - 应用通用模块

**模块定位**: 包含应用通用定义，但不包含核心，依赖核心

**核心功能**: 应用通用定义

**数据表前缀**: `application_`

---

### Demo5 - 演示模块

**模块定位**: 演示模块化最佳实践的完整功能模块

**核心功能**:
- 服务层架构（Services + Logics）
- 事件驱动系统（Events + Listeners）
- 钩子系统（Hooks）
- 队列任务处理（QueueJobs）
- 数据传输对象（DTOs）
- 枚举类型管理（Enums）
- 自定义模型类型转换（Casts）
- Artisan 命令工具

**数据表前缀**: `demo5_`

---

## 四、前台模块层

### DcatAdmin - 超管后台模块

**模块定位**: 超管后台 Web 界面，系统级管理

**核心功能**:
- 仪表盘管理（系统概览和统计信息）
- 缓存管理（多种缓存类型的清理和管理）
- 统计图表（数据可视化组件）
- 日志记录（管理操作日志系统）
- 系统工具（系统管理实用工具）

**路由前缀**: `/admin`

**用户角色**: 超级管理员（系统运营方）

**数据表前缀**: `dcatadmin_`

---

## 五、模板模块

### Emptyarch - 空架构模板模块

**模块定位**: 作为创建新模块的模板，包含完整的目录结构和基础文件

**用途**: 
- 使用 `php artisan module:make-arch {ModuleName}` 创建新模块时作为模板
- 包含完整目录结构、命名空间替换、module.json 配置

**状态**: 未启用（modules_statuses.json 中 false）

⚠️ **重要提示**: 此模块为架构模板，不应用于实际业务开发

---

## 六、调试工具

### Debug - 调试模块

**模块定位**: 系统调试、日志分析、状态监控

**核心功能**:
- 日志查看和分析
- 系统状态监控
- 缓存状态检查
- 性能分析

**入口形式**:
- Commands/ - Artisan 命令入口
- Api/ - RESTful API 入口

---

## 七、演示模块

### Demo5 - 演示模块

**模块定位**: 演示模块化最佳实践的完整功能模块

**核心功能**:
- 服务层架构（Services + Logics）
- 事件驱动系统（Events + Listeners）
- 钩子系统（Hooks）
- 队列任务处理（QueueJobs）
- 数据传输对象（DTOs）
- 枚举类型管理（Enums）
- 自定义模型类型转换（Casts）
- Artisan 命令工具

**数据表前缀**: `demo5_`

---

## 八、模块架构特点总结

### 单模块全栈架构

大部分业务/功能模块采用"单模块全栈架构"，包含：

- **ApiProto/**: SaaS 管理端 API 入口（部分模块）
- **DcatAdmin/**: 超管后台 Web 入口
- **Models/**: 数据模型层
- **Services/**: 业务服务层
- **Logics/**: 逻辑层
- **Enums/**: 枚举定义
- **Validations/**: 验证类
- **Events/**: 事件类
- **Listeners/**: 监听器
- **protos/**: 定义文件

### 双管理入口分离

- **超管后台**（DcatAdmin Controllers）: `/admin`，系统级管理
- **SaaS管理端**（ApiProto Handlers）: `/api/proto`，租户级管理（部分模块支持）
- **共享业务逻辑**: 两个入口共享同一 Models/Services/Logics 层

### 模块间通信

- **通过 Event**: 异步事件驱动
- **通过 Hook**: 同步 Hook 调用
- **禁止跨模块调用**: 禁止跨模块调用 Model/Service

---

## 九、模块启用状态

**总模块数**: 20 个

**已启用**: 19 个
- NnnnMain, ABase, AFile, Application, ASync, China, Cms, DcatAdmin, Debug, Demo5
- FeatureAi, FeatureExcel, FeatureExcelDemo, FeatureSms, FeatureSsh, Notification, NnnAgent, NnnProject

**未启用**: 1 个
- Emptyarch（模板模块，不参与业务）

---

## 十、开发规范

### 必须遵守

1. **模块化开发**: 所有代码在 `Modules/` 内，禁止修改 `app/`
2. **数据表前缀**: 每个模块的数据表使用模块名作为前缀
3. **PHPDoc 规范**: 所有代码必须有注释
4. **禁止 try**: 非必要不要 try，会掩盖错误
5. **禁止构造函数属性提升语法**
6. **避免服务容器/依赖注入/Facades**

### 分层架构（单模块内部）

```
Models → Services → Logics → Controllers(模块内的DcatAdmin/Api入口)
```

| 层级 | 职责 | 关键规则 |
|------|------|---------|
| **Models** | 数据结构、关系映射 | Eloquent ORM，数据表使用模块名前缀 |
| **Services** | 协调组件、复杂业务 | **优先静态方法**，**禁止读取HTTP/session** |
| **Logics** | 数据组装/单一逻辑 | **必须静态类**，不允许实例化，无状态 |
| **DcatAdmin Controllers** | 超管后台HTTP处理 | Grid/Form/Action，系统级管理 |
| **ApiProto Handlers** | SaaS管理端API Handler | **禁止直接操作数据库**，需通过 Services 层 |

---

## 十一、模块依赖关系图

```
核心层：ABase（所有模块依赖）
    ↓
业务层：NnnnMain（主模块协调）
        NnnAgent（AI Agent）
    ↓
功能层：Feature* 系列（功能模块）
        AFile/Notification/ASync/Cms（业务支持）
    ↓
前台层：DcatAdmin（超管后台）
```

---

## 十二、快速导航

### 核心模块
- [ABase](./ABase/) - 基础工具模块

### 业务模块
- [NnnnMain](./NnnnMain/) - 主模块
- [NnnAgent](./NnnAgent/) - AI Agent 模块
- [NnnProject](./NnnProject/) - 项目管理模块

### 功能模块
- [FeatureAi](./FeatureAi/) - AI 功能
- [FeatureAi](./FeatureAi/) - AI 功能
- [FeatureExcel](./FeatureExcel/) - Excel 导入导出引擎
- [FeatureExcelDemo](./FeatureExcelDemo/) - FeatureExcel 演示
- [FeatureSms](./FeatureSms/) - 短信功能
- [FeatureSsh](./FeatureSsh/) - SSH 功能

### 业务支持模块
- [AFile](./AFile/) - 文件管理
- [Notification](./Notification/) - 消息通知
- [ASync](./ASync/) - 数据同步
- [Cms](./Cms/) - 内容管理
- [China](./China/) - 中国区数据
- [Application](./Application/) - 应用通用

### 前台模块
- [DcatAdmin](./DcatAdmin/) - 超管后台

### 演示与调试
- [Demo5](./Demo5/) - 演示模块
- [Debug](./Debug/) - 调试模块

### 模板模块
- [Emptyarch](./Emptyarch/) - 空架构模板（创建新模块用）

---

**更新时间**: 2026-09-02
**维护者**: AI 开发团队