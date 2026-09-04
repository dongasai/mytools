---
name: dev-seeder
description: 进行数据库Seeder开发时必须使用
---

# Seeder编写指南

- 要有主Seeder
- 每个表独立Seeder
  - Seeder文件不宜过大,如有必要进行拆分
- 主Seeder
  * 清理模块的每个表
  * 按照依赖顺序逐步执行每个Seeder
- Seeder规划文档，按照 Modules/Demo5/Database/Seeder.md 的规范
- 编写之前先通过迁移文件和模型了解表结构
- 注意模块边界,Seeder只处理本模块的表

查看  Modules/Demo5/Database/Seeder.md了解规则文档规范