# 数据库树四级结构设计

## 需求
将连接树从二级改为四级：
1. 连接（一级）
2. 数据库（二级）
3. 模式（三级）
4. 表/其他操作（四级）

## 数据库特性差异

### MySQL
- **数据库**: 支持多数据库（SHOW DATABASES）
- **模式**: MySQL 中 schema ≈ database，简化为无模式层级
- **建议**: 连接 -> 数据库 -> 表（三级）

### PostgreSQL
- **数据库**: 支持多数据库（pg_database）
- **模式**: 每个数据库有多个 schema（public, user_schema 等）
- **建议**: 连接 -> 数据库 -> 模式 -> 表（四级）

### SQLite
- **数据库**: 单文件单数据库
- **模式**: 无 schema 概念
- **建议**: 连接 -> 表（二级）

## 技术方案

### 后端驱动层
已添加方法：
- `getDatabases()`: 获取数据库列表
- `getSchemas($database)`: 获取模式列表
- `getAllTables($database, $schema)`: 获取表列表

### 前端树结构
```javascript
{
  id: 'conn-1',
  label: 'MySQL连接',
  type: 'connection',
  children: [
    {
      id: 'conn-1-db-sanniu',
      label: 'sanniu',
      type: 'database',
      children: [
        {
          id: 'conn-1-db-sanniu-sql',
          label: 'SQL 执行',
          type: 'sql'
        },
        {
          id: 'conn-1-db-sanniu-tables',
          label: '表',
          type: 'table_folder',
          children: [
            { id: 'table-1-users', label: 'users', type: 'table' },
            { id: 'table-1-orders', label: 'orders', type: 'table' }
          ]
        }
      ]
    }
  ]
}
```

## 待实现

1. ✅ 驱动接口定义
2. ✅ MySQL 驱动实现
3. ✅ PostgreSQL 驱动实现
4. ⏳ SQLite 驱动实现
5. ⏳ 后端 API 接口
6. ⏳ 前端树结构调整

## 简化方案
考虑到数据库差异，建议采用智能适配：
- PostgreSQL: 四级结构
- MySQL: 三级结构（数据库 -> 表）
- SQLite: 二级结构（表）