# FeatureDbadmin 模块开发计划

## 项目信息

**模块名称**: FeatureDbadmin（数据库管理员模块）
**开发时间**: 2026-09-08
**预计工期**: 13 天
**开发模式**: 单模块全栈架构 + Vue 前端

---

## 架构模式

- ✅ **只有后台**（Dcat Admin 超管后台）
- ❌ **没有 Web 前台**
- ❌ **没有 API 分组**（无 ApiProto）
- ✅ **Vue 页面**（嵌入到 Dcat Admin）
- ✅ **混合返回**：HTML 页面 + JSON 接口

---

## 数据表设计（共5张表）

### 1. feature_dbadmin_connections - 数据库连接配置表
- 字段：连接名称、驱动、主机、端口、数据库、用户名、密码（明文）
- 功能：管理多个数据库连接配置

### 2. feature_dbadmin_query_histories - SQL 查询历史表
- 字段：用户ID、连接名、SQL语句、查询类型、执行时间、行数、状态
- 功能：记录查询历史

### 3. feature_dbadmin_saved_queries - 保存的查询表
- 字段：用户ID、查询名称、SQL语句、标签、是否公开
- 功能：保存常用查询

### 4. feature_dbadmin_favorite_tables - 收藏的表
- 字段：用户ID、连接名、表名、别名、备注
- 功能：收藏常用表

### 5. feature_dbadmin_table_snapshots - 表结构快照表
- 字段：连接名、表名、表结构JSON、列数、索引数、快照时间
- 功能：保存表结构历史快照

---

## 开发阶段

### 阶段1: 基础设施 + 后端服务（优先级：高）
**时间**: 3 天
**状态**: ✅ 已完成

#### 1.1 数据库迁移文件
- [x] `2026_09_08_000001_create_feature_dbadmin_connections_table.php`
- [x] `2026_09_08_000002_create_feature_dbadmin_query_histories_table.php`
- [x] `2026_09_08_000003_create_feature_dbadmin_saved_queries_table.php`
- [x] `2026_09_08_000004_create_feature_dbadmin_favorite_tables_table.php`
- [x] `2026_09_08_000005_create_feature_dbadmin_table_snapshots_table.php`

#### 1.2 Models 层
- [x] `Connection.php` - 连接配置模型
  - 密码明文存储
  - `testConnection()` 方法
  - `toConfigArray()` 方法
- [x] `QueryHistory.php` - 查询历史模型
- [x] `SavedQuery.php` - 保存的查询模型
- [x] `FavoriteTable.php` - 收藏的表模型
- [x] `TableSnapshot.php` - 表结构快照模型

#### 1.3 Enums 层
- [x] `QueryType.php` - 查询类型枚举（SELECT/INSERT/UPDATE/DELETE/DDL）
- [x] `QueryStatus.php` - 查询状态枚举（SUCCESS/FAILED）
- [x] `ConnectionDriver.php` - 数据库驱动枚举（MYSQL/PGSQL/SQLITE）

#### 1.4 DTOs 层
- [x] `TableStructureDto.php` - 表结构DTO
- [x] `ColumnInfoDto.php` - 列信息DTO
- [x] `IndexInfoDto.php` - 索引信息DTO
- [x] `QueryResultDto.php` - 查询结果DTO

#### 1.5 Services 层
- [x] `DatabaseService.php` - 数据库连接管理服务
  - `getConnections()` - 获取所有连接
  - `testConnection()` - 测试连接
  - `switchConnection()` - 切换连接
- [x] `TableService.php` - 表结构管理服务
  - `getAllTables()` - 获取所有表
  - `getColumns()` - 获取列信息
  - `getIndexes()` - 获取索引信息
  - `getForeignKeys()` - 获取外键信息
  - `getTableRowCount()` - 获取行数
  - `getTableSize()` - 获取表大小
- [x] `QueryService.php` - SQL 查询执行服务
  - `executeQuery()` - 执行查询
  - `validateQuery()` - 验证查询
  - `parseQueryType()` - 解析查询类型
  - `saveQueryHistory()` - 保存历史
- [x] `DataBrowserService.php` - 数据浏览服务
  - `getTableData()` - 获取表数据（分页）
  - `updateRow()` - 更新数据
  - `insertRow()` - 插入数据
  - `deleteRow()` - 删除数据
- [x] `ExportService.php` - 导出服务
  - `exportStructureToSql()` - 导出结构为SQL
  - `exportDataToCsv()` - 导出数据为CSV
  - `exportDataToJson()` - 导出数据为JSON

#### 1.6 Logics 层
- [x] `DatabaseLogic.php` - 数据库操作逻辑
- [x] `TableStructureLogic.php` - 表结构解析逻辑
- [x] `QueryParserLogic.php` - SQL 解析逻辑
- [x] `DataFormatterLogic.php` - 数据格式化逻辑

---

### 阶段2: 后端 JSON 接口（优先级：高）
**时间**: 2 天
**状态**: ⏸️ 待开始

#### 2.1 ConnectionController
- [ ] `index()` - HTML 页面
- [ ] `list()` - JSON 接口：连接列表
- [ ] `store()` - JSON 接口：创建连接
- [ ] `update()` - JSON 接口：更新连接
- [ ] `destroy()` - JSON 接口：删除连接
- [ ] `test()` - JSON 接口：测试连接

#### 2.2 TableController
- [ ] `index()` - HTML 页面
- [ ] `list()` - JSON 接口：表列表
- [ ] `structure()` - JSON 接口：表结构详情
- [ ] `export()` - JSON 接口：导出表结构

#### 2.3 DataBrowserController
- [ ] `index()` - HTML 页面
- [ ] `list()` - JSON 接口：数据列表
- [ ] `row()` - JSON 接口：单行数据
- [ ] `create()` - JSON 接口：新增数据
- [ ] `update()` - JSON 接口：更新数据
- [ ] `delete()` - JSON 接口：删除数据

#### 2.4 QueryToolController
- [ ] `index()` - HTML 页面
- [ ] `execute()` - JSON 接口：执行查询
- [ ] `history()` - JSON 接口：查询历史
- [ ] `save()` - JSON 接口：保存查询

#### 2.5 DashboardController
- [ ] `index()` - HTML 页面
- [ ] `stats()` - JSON 接口：统计数据

---

### 阶段3: Vue 前端页面（优先级：中）
**时间**: 5 天
**状态**: ⏸️ 待开始

#### 3.1 构建工具配置
- [ ] 安装 Vue 3
- [ ] 安装 Element Plus
- [ ] 安装 Monaco Editor（SQL 编辑器）
- [ ] 配置 vite.config.js
- [ ] 配置 resources/js/app.js

#### 3.2 Vue 组件开发
- [ ] `Dashboard.vue` - 仪表盘
  - 连接统计卡片
  - 最近查询历史
  - 快捷操作入口
- [ ] `ConnectionManager.vue` - 连接管理
  - 连接列表（卡片）
  - 创建/编辑表单
  - 测试连接功能
- [ ] `TableManager.vue` - 表管理
  - 表列表（表格）
  - 表结构查看（模态框）
  - 表结构导出
  - 收藏表功能
- [ ] `DataBrowser.vue` - 数据浏览
  - 数据表格展示（分页、排序、筛选）
  - 行内编辑
  - 数据新增/删除
  - 数据导出
- [ ] `QueryTool.vue` - SQL 查询工具
  - SQL 编辑器（Monaco Editor）
  - 查询执行
  - 结果展示（表格）
  - 查询历史
  - 保存查询

#### 3.3 公共组件
- [ ] `TableCard.vue` - 表格卡片组件
- [ ] `SqlEditor.vue` - SQL 编辑器组件
- [ ] `Pagination.vue` - 分页组件
- [ ] `Loading.vue` - 加载组件

---

### 阶段4: Blade 视图和路由（优先级：中）
**时间**: 1 天
**状态**: ⏸️ 待开始

#### 4.1 Blade 视图
- [ ] `resources/views/dashboard/index.blade.php`
- [ ] `resources/views/connections/index.blade.php`
- [ ] `resources/views/tables/index.blade.php`
- [ ] `resources/views/data/index.blade.php`
- [ ] `resources/views/query/index.blade.php`

#### 4.2 路由配置
- [ ] HTML 路由（页面）
- [ ] JSON 路由（接口）
- [ ] 权限控制

---

### 阶段5: 测试和优化（优先级：低）
**时间**: 2 天
**状态**: ⏸️ 待开始

#### 5.1 功能测试
- [ ] 连接管理测试
  - 创建连接
  - 测试连接
  - 切换连接
  - 删除连接
- [ ] 表管理测试
  - 查看表列表
  - 查看表结构
  - 导出表结构
- [ ] 数据浏览测试
  - 浏览数据
  - 编辑数据
  - 新增/删除数据
- [ ] SQL 查询测试
  - 执行 SELECT
  - 执行 INSERT/UPDATE/DELETE
  - 查询历史
  - 保存查询

#### 5.2 性能优化
- [ ] 查询优化（大表查询）
- [ ] 缓存优化（表结构缓存）
- [ ] 分页优化

#### 5.3 UI 美化
- [ ] 样式优化
- [ ] 响应式布局
- [ ] 暗色主题适配

---

## 技术要点

### 1. 动态数据库连接

```php
// 动态创建连接
$connection = Connection::find($id);
$connectionName = 'dynamic_' . $connection->id;

Config::set("database.connections.{$connectionName}", $connection->toConfigArray());
DB::purge($connectionName);

// 使用连接
DB::connection($connectionName)->select('...');
```

### 2. 多数据库驱动支持

```php
// MySQL
SHOW TABLES;
SHOW CREATE TABLE `table_name`;

// PostgreSQL
SELECT tablename FROM pg_tables WHERE schemaname = 'public';
SELECT * FROM information_schema.columns WHERE table_name = 'table_name';

// SQLite
SELECT name FROM sqlite_master WHERE type='table';
PRAGMA table_info(table_name);
```

### 3. Vue 组件嵌入 Dcat Admin

```blade
@extends('admin::content')

@section('content')
<div id="vue-app">
    <table-manager></table-manager>
</div>

<script>
  window.initialData = {
    connections: {!! $connections !!}
  };
</script>
<script src="{{ asset('js/featuredbadmin/app.js') }}"></script>
@endsection
```

### 4. SQL 安全验证

```php
// 禁止的危险操作
$dangerousKeywords = ['DROP', 'TRUNCATE', 'GRANT', 'REVOKE', 'ALTER USER'];
```

---

## 开发规范

### 必须遵守

1. **数据表前缀**: 所有表使用 `feature_dbadmin_` 前缀
2. **PHPDoc 规范**: 所有代码必须有完整注释
3. **禁止 try**: 非必要不要 try，会掩盖错误
4. **禁止构造函数属性提升语法**
5. **优先使用静态方法**: Service 层方法均为静态方法
6. **禁止跨模块调用**: 不直接调用其他模块的 Model/Service
7. **参数显性传入**: Service 层禁止读取 HTTP/session
8. **密码明文存储**: 数据库连接密码不加密

### 分层规范

```
Models → Services → Logics → Controllers
                              ├── HTML 页面（Blade）
                              └── JSON 接口（Vue 使用）
```

---

## 关键文件清单

### Models
- `Modules/FeatureDbadmin/Models/Connection.php`
- `Modules/FeatureDbadmin/Models/QueryHistory.php`
- `Modules/FeatureDbadmin/Models/SavedQuery.php`
- `Modules/FeatureDbadmin/Models/FavoriteTable.php`
- `Modules/FeatureDbadmin/Models/TableSnapshot.php`

### Services
- `Modules/FeatureDbadmin/Services/DatabaseService.php`
- `Modules/FeatureDbadmin/Services/TableService.php`
- `Modules/FeatureDbadmin/Services/QueryService.php`
- `Modules/FeatureDbadmin/Services/DataBrowserService.php`
- `Modules/FeatureDbadmin/Services/ExportService.php`

### Controllers
- `Modules/FeatureDbadmin/DcatAdmin/Controllers/DashboardController.php`
- `Modules/FeatureDbadmin/DcatAdmin/Controllers/ConnectionController.php`
- `Modules/FeatureDbadmin/DcatAdmin/Controllers/TableController.php`
- `Modules/FeatureDbadmin/DcatAdmin/Controllers/DataBrowserController.php`
- `Modules/FeatureDbadmin/DcatAdmin/Controllers/QueryToolController.php`

### Vue 组件
- `Modules/FeatureDbadmin/resources/js/components/Dashboard.vue`
- `Modules/FeatureDbadmin/resources/js/components/ConnectionManager.vue`
- `Modules/FeatureDbadmin/resources/js/components/TableManager.vue`
- `Modules/FeatureDbadmin/resources/js/components/DataBrowser.vue`
- `Modules/FeatureDbadmin/resources/js/components/QueryTool.vue`

---

## 进度追踪

**当前阶段**: 阶段2 - 后端 JSON 接口
**当前任务**: 创建 Controllers
**完成进度**: 20% (阶段1已完成)
**开始时间**: 2026-09-08
**预计完成**: 2026-09-21

**已完成的阶段**:
- ✅ 阶段1: 基础设施 + 后端服务 (100%)

---

**更新时间**: 2026-09-08
**维护者**: 开发团队