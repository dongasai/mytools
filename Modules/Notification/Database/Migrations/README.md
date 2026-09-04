# migrations 目录

数据库迁移文件目录。

## 职责
- 存放数据库表结构迁移文件
- 创建、修改、删除表结构

## 命名规范
- `{timestamp}_create_{table}_table.php`
- 示例: `2026_06_27_000001_create_notification_logs_table.php`

## 注意事项
- **迁移文件创建后不执行，由人工执行**
- 使用匿名类方式: `return new class extends Migration`