# 数据库树智能适配实施计划

## 已完成 ✅

### 1. 后端驱动层
- ✅ 接口定义：添加 `getDatabases()` 和 `getSchemas()` 方法
- ✅ MySqlDriver 实现
- ✅ PgSqlDriver 实现（已修复 3 个 bug）
- ✅ SqliteDriver 实现

### 2. 后端 API
- ✅ 路由添加：`/databases` 和 `/schemas`
- ✅ 控制器方法：`databases()` 和 `schemas()`
- ✅ 表列表支持：支持 `database` 和 `schema` 参数

### 3. 前端树结构改造
- ✅ App.vue 懒加载实现
- ✅ 智能适配逻辑（不同数据库不同层级）
- ✅ TableList.vue 支持 database/schema 参数
- ✅ DataBrowser.vue 支持 database/schema 参数
- ✅ QueryTool.vue 支持 database/schema 参数
- ✅ 前端编译完成

### 4. Bug 修复
- ✅ PostgreSQL 表注释查询错误修复（使用 to_regclass）
- ✅ PostgreSQL 表大小查询错误修复（参数绑定）
- ✅ PostgreSQL 索引列数组类型错误修复（array_to_string）

## 待测试 ⏳

### 功能测试
需要 Web 服务器配置修复后进行完整测试：
- PostgreSQL 四级树结构测试
- MySQL 三级树结构测试
- SQLite 二级树结构测试
- 表创建和数据操作测试

### 性能测试
- 懒加载性能
- 大量表情况下的加载速度

## 待实施 ⏳

### 3. 前端树结构改造

#### 方案：懒加载 + 智能适配

```vue
<el-tree
  :data="connectionsTree"
  :props="treeProps"
  node-key="id"
  :load="loadNode"  <!-- 懒加载 -->
  lazy
/>
```

#### 节点类型定义

| 类型 | 层级 | 子节点 |
|------|------|--------|
| connection | 1 | database 或 sql+tables |
| database | 2 | schema 或 sql+tables |
| schema | 3 | sql+tables |
| sql | N/A | 无子节点 |
| table_folder | N/A | table 列表 |
| table | N/A | 无子节点 |

#### 智能适配逻辑

```javascript
const loadNode = async (node, resolve) => {
  if (node.level === 0) {
    // 加载连接列表
    const connections = await loadConnections()
    resolve(connections)
  } else if (node.data.type === 'connection') {
    // 根据驱动类型决定子节点
    if (node.data.driver === 'pgsql') {
      // PostgreSQL: 加载数据库列表
      const databases = await loadDatabases(node.data.connectionId)
      resolve(databases)
    } else if (node.data.driver === 'mysql') {
      // MySQL: 加载数据库列表
      const databases = await loadDatabases(node.data.connectionId)
      resolve(databases)
    } else {
      // SQLite: 直接显示 SQL+表
      resolve([
        { type: 'sql', label: 'SQL 执行' },
        { type: 'table_folder', label: '表' }
      ])
    }
  } else if (node.data.type === 'database') {
    // 数据库节点：加载模式或表
    if (需要模式层级) {
      const schemas = await loadSchemas(...)
      resolve(schemas)
    } else {
      resolve([...sql 和 tables])
    }
  }
  // ... 其他层级
}
```

### 4. 实施步骤

1. **修改树配置**
   - 启用 lazy 懒加载
   - 实现 loadNode 方法

2. **实现加载函数**
   - loadDatabases()
   - loadSchemas()
   - loadTables()

3. **修改节点点击处理**
   - 支持不同层级的节点点击

4. **更新路由参数**
   - TableList 支持传递 database 和 schema
   - DataBrowser 支持传递 database 和 schema

## 文件清单

### 需修改
- `Modules/FeatureDbadmin/resources/js/App.vue`
  - 树结构改为懒加载
  - 添加 loadNode 方法
  - 修改节点点击处理

- `Modules/FeatureDbadmin/resources/js/views/TableList.vue`
  - 支持接收 database 和 schema 参数

- `Modules/FeatureDbadmin/resources/js/router/index.js`
  - 路由支持 database 和 schema 参数

### 保持不变
- 后端驱动层（已完成）
- 后端 API（已完成）
- 其他 Vue 组件

## 测试计划

### PostgreSQL
1. 展开连接 → 应显示数据库列表
2. 展开数据库 → 应显示模式列表
3. 展开模式 → 应显示 SQL+表列表
4. 点击表 → 应打开数据浏览

### MySQL
1. 展开连接 → 应显示数据库列表
2. 展开数据库 → 应显示 SQL+表列表
3. 点击表 → 应打开数据浏览

### SQLite
1. 展开连接 → 应显示 SQL+表列表
2. 点击表 → 应打开数据浏览