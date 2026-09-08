# FeatureDbadmin 模块开发方案

## 一、模块概述

### 模块定位
数据库管理员工具模块，提供数据库/模式/表的 CRUD 操作，帮助超级管理员可视化管理数据库。

### 核心功能
- 数据库连接管理（创建/删除/查看）
- Schema 模式管理（创建/删除/权限管理）
- 表结构管理（查看/导出/创建/修改/删除）
- 数据管理（浏览/编辑/导入导出）
- SQL 查询工具（执行/历史/格式化）

### 架构模式
- ✅ **只有后台**（Dcat Admin 超管后台）
- ❌ **没有 Web 前台**
- ❌ **没有 API 分组**（无 ApiProto）
- ✅ **Vue 页面**（嵌入到 Dcat Admin）
- ✅ **混合返回**：HTML 页面 + JSON 接口

### 技术栈
- **后端**: Laravel 12 + Dcat Admin 2.x
- **前端**: Vue 3 + Element Plus + Monaco Editor
- **构建**: Vite
- **数据表前缀**: `feature_dbadmin_`

---

## 二、数据库表设计

### 表1: `feature_dbadmin_query_histories` - SQL 查询历史表

| 字段名 | 类型 | 说明 |
|--------|------|------|
| id | bigint unsigned | 主键 |
| user_id | int unsigned | 执行用户ID |
| connection_name | varchar(50) | 数据库连接名 |
| sql_query | text | SQL 查询语句 |
| query_type | varchar(20) | 查询类型（SELECT/INSERT/UPDATE/DELETE/DDL） |
| execution_time | int unsigned | 执行时间（毫秒） |
| row_count | int | 影响行数 |
| status | varchar(20) | 执行状态 |
| error_message | text nullable | 错误信息 |
| executed_at | timestamp | 执行时间 |

**索引**: user_id, connection_name, executed_at, query_type

### 表2: `feature_dbadmin_saved_queries` - 保存的查询表

| 字段名 | 类型 | 说明 |
|--------|------|------|
| id | bigint unsigned | 主键 |
| user_id | int unsigned | 用户ID |
| name | varchar(200) | 查询名称 |
| description | text nullable | 查询描述 |
| connection_name | varchar(50) | 数据库连接名 |
| sql_query | text | SQL 查询语句 |
| tags | json nullable | 标签 |
| is_public | tinyint(1) | 是否公开 |
| use_count | int unsigned | 使用次数 |
| last_used_at | timestamp nullable | 最后使用时间 |

**索引**: user_id, is_public, connection_name

### 表3: `feature_dbadmin_favorite_tables` - 收藏的表

| 字段名 | 类型 | 说明 |
|--------|------|------|
| id | bigint unsigned | 主键 |
| user_id | int unsigned | 用户ID |
| connection_name | varchar(50) | 数据库连接名 |
| table_name | varchar(100) | 表名 |
| schema_name | varchar(100) nullable | 模式名 |
| alias | varchar(100) nullable | 别名 |
| notes | text nullable | 备注 |

**唯一索引**: user_id + connection_name + table_name + schema_name

### 表4: `feature_dbadmin_table_snapshots` - 表结构快照表

| 字段名 | 类型 | 说明 |
|--------|------|------|
| id | bigint unsigned | 主键 |
| connection_name | varchar(50) | 数据库连接名 |
| table_name | varchar(100) | 表名 |
| schema_name | varchar(100) nullable | 模式名 |
| table_structure | json | 表结构JSON |
| column_count | int unsigned | 列数 |
| index_count | int unsigned | 索引数 |
| foreign_key_count | int unsigned | 外键数 |
| row_count | bigint unsigned | 行数 |
| table_size | varchar(50) nullable | 表大小 |
| snapshot_at | timestamp | 快照时间 |

---

## 三、模块架构

### 分层架构
```
Models → Services → Logics → Controllers(DcatAdmin)
                              ├── HTML 页面（Blade）
                              └── JSON 接口（供 Vue 使用）
```

### 核心目录结构
```
FeatureDbadmin/
├── Models/              # 数据模型层
│   ├── Connection.php
│   ├── QueryHistory.php
│   ├── SavedQuery.php
│   ├── FavoriteTable.php
│   └── TableSnapshot.php
│
├── Services/            # 业务服务层（静态方法）
│   ├── DatabaseService.php
│   ├── TableService.php
│   ├── QueryService.php
│   ├── DataBrowserService.php
│   └── ExportService.php
│
├── Logics/              # 逻辑层（静态类）
│   ├── DatabaseLogic.php
│   ├── TableStructureLogic.php
│   ├── QueryParserLogic.php
│   └── DataFormatterLogic.php
│
├── DcatAdmin/           # 超管后台入口
│   ├── Controllers/     # 控制器（HTML + JSON）
│   │   ├── DashboardController.php
│   │   ├── ConnectionController.php
│   │   ├── TableController.php
│   │   ├── DataBrowserController.php
│   │   └── QueryToolController.php
│   └── Repositories/    # 数据仓库
│
├── resources/           # 前端资源
│   ├── views/           # Blade 视图
│   │   ├── dashboard/
│   │   ├── connections/
│   │   ├── tables/
│   │   ├── data/
│   │   └── query/
│   └── js/              # Vue 组件
│       ├── components/
│       │   ├── Dashboard.vue
│       │   ├── ConnectionManager.vue
│       │   ├── TableManager.vue
│       │   ├── DataBrowser.vue
│       │   └── QueryTool.vue
│       └── app.js
│
├── Enums/               # 枚举类
├── Dtos/                # 数据传输对象
├── Events/              # 事件类
├── Listeners/           # 监听器
└── Validations/         # 验证类
```

---

## 四、核心服务设计

### DatabaseService - 数据库连接管理
- `getConnections()` - 获取所有连接
- `getConnectionConfig()` - 获取连接配置
- `testConnection()` - 测试连接
- `switchConnection()` - 切换连接

### TableService - 表结构管理
- `getAllTables()` - 获取所有表
- `getColumns()` - 获取列信息
- `getIndexes()` - 获取索引信息
- `getForeignKeys()` - 获取外键信息
- `getTableRowCount()` - 获取行数
- `getTableSize()` - 获取表大小

### QueryService - SQL 查询执行
- `executeQuery()` - 执行查询
- `validateQuery()` - 验证查询
- `parseQueryType()` - 解析查询类型
- `saveQueryHistory()` - 保存历史

### DataBrowserService - 数据浏览
- `getTableData()` - 获取表数据（分页）
- `updateRow()` - 更新数据
- `insertRow()` - 插入数据
- `deleteRow()` - 删除数据
- `searchData()` - 搜索数据

---

## 五、开发阶段

### 阶段1: 基础设施 + 后端服务（优先级：高）
**时间**: 3 天

- [ ] 创建 5 个迁移文件
  - [ ] `feature_dbadmin_connections` - 数据库连接配置表
  - [ ] `feature_dbadmin_query_histories` - 查询历史表
  - [ ] `feature_dbadmin_saved_queries` - 保存的查询表
  - [ ] `feature_dbadmin_favorite_tables` - 收藏的表
  - [ ] `feature_dbadmin_table_snapshots` - 表结构快照表
- [ ] 创建 5 个 Models
- [ ] 创建 Enums 和 DTOs
- [ ] 创建 Services（DatabaseService、TableService、QueryService）

### 阶段2: 后端 JSON 接口（优先级：高）
**时间**: 2 天

- [ ] ConnectionController（JSON 接口）
  - [ ] `list()` - 连接列表
  - [ ] `store()` - 创建连接
  - [ ] `update()` - 更新连接
  - [ ] `destroy()` - 删除连接
  - [ ] `test()` - 测试连接
- [ ] TableController（JSON 接口）
  - [ ] `list()` - 表列表
  - [ ] `structure()` - 表结构
  - [ ] `export()` - 导出结构
- [ ] DataBrowserController（JSON 接口）
  - [ ] `list()` - 数据列表
  - [ ] `row()` - 单行数据
  - [ ] `create()` - 新增数据
  - [ ] `update()` - 更新数据
  - [ ] `delete()` - 删除数据
- [ ] QueryToolController（JSON 接口）
  - [ ] `execute()` - 执行查询
  - [ ] `history()` - 查询历史
  - [ ] `save()` - 保存查询

### 阶段3: Vue 前端页面（优先级：中）
**时间**: 5 天

- [ ] 配置 Vite 构建
  - [ ] 安装 Vue 3
  - [ ] 安装 Element Plus
  - [ ] 安装 Monaco Editor
  - [ ] 配置 vite.config.js
- [ ] 创建 Vue 组件
  - [ ] `Dashboard.vue` - 仪表盘
  - [ ] `ConnectionManager.vue` - 连接管理
  - [ ] `TableManager.vue` - 表管理
  - [ ] `DataBrowser.vue` - 数据浏览
  - [ ] `QueryTool.vue` - SQL 查询工具
- [ ] 创建公共组件
  - [ ] `TableCard.vue` - 表格卡片
  - [ ] `SqlEditor.vue` - SQL 编辑器
  - [ ] `Pagination.vue` - 分页组件

### 阶段4: Blade 视图和路由（优先级：中）
**时间**: 1 天

- [ ] 创建 Blade 视图
  - [ ] `dashboard/index.blade.php`
  - [ ] `connections/index.blade.php`
  - [ ] `tables/index.blade.php`
  - [ ] `data/index.blade.php`
  - [ ] `query/index.blade.php`
- [ ] 配置路由
  - [ ] HTML 路由（页面）
  - [ ] JSON 路由（接口）

### 阶段5: 测试和优化（优先级：低）
**时间**: 2 天

- [ ] 功能测试
  - [ ] 连接管理测试
  - [ ] 表管理测试
  - [ ] 数据浏览测试
  - [ ] SQL 查询测试
- [ ] 性能优化
  - [ ] 查询优化
  - [ ] 缓存优化
- [ ] UI 美化

---

## 六、关键技术点

### 动态 Grid/Form 实现
根据表结构动态生成 Grid 列和 Form 字段。

### 多数据库连接支持
支持 SQLite、MySQL、PostgreSQL，根据驱动获取不同的系统表 SQL。

### SQL 安全验证
防止危险 SQL 执行（DROP、TRUNCATE 等）。

### 查询历史记录
使用事件系统自动记录所有查询历史。

### 表结构快照对比
对比表结构变化，识别新增、删除、修改的列。

---

## 七、开发规范

### 必须遵守
1. 数据表前缀: `feature_dbadmin_`
2. PHPDoc 规范: 所有代码必须有注释
3. 禁止 try: 非必要不要 try
4. 禁止构造函数属性提升语法
5. 优先使用静态方法: Service 层方法均为静态方法
6. 禁止跨模块调用
7. 参数显性传入

---

**创建时间**: 2026-09-08
**维护者**: 开发团队