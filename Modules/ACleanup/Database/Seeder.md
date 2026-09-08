# Cleanup 模块 Seeder 文档

## 概述

Cleanup 模块的 Seeder 用于初始化清理配置和清理计划的基础数据。

## 可用的 Seeder

### 1. CleanupDatabaseSeeder
- **作用**: 主 Seeder，负责调用其他 Seeder
- **使用方式**:
  ```bash
  php artisan module:seed Cleanup
  ```

### 2. CleanupConfigSeeder
- **作用**: 填充清理配置数据
- **功能**:
  - 创建默认的清理配置
  - 为常用数据表设置清理规则
  - 配置清理类型和默认条件

- **配置内容**:
  - **admin_action_logs**: 管理员操作日志，保留90天
  - **user_profiles**: 已删除用户档案数据
  - **failed_jobs**: 失败任务队列，保留24小时

### 3. CleanupPlanSeeder
- **作用**: 填充清理计划数据
- **功能**:
  - 创建示例清理计划
  - 设置计划内容和执行规则
  - 配置备份选项

- **计划类型**:
  - **日志清理计划**: 分类清理日志数据
  - **用户数据清理**: 模块清理用户相关数据

## 使用方法

### 运行所有 Seeder
```bash
php artisan module:seed Cleanup
```

### 运行特定 Seeder
```bash
php artisan module:seed Cleanup --class=CleanupConfigSeeder
php artisan module:seed Cleanup --class=CleanupPlanSeeder
```

### 在代码中调用
```php
use Modules\Cleanup\Database\Seeders\CleanupDatabaseSeeder;

$seeder = new CleanupDatabaseSeeder();
$seeder->run();
```

## 配置说明

### 数据分类 (data_category)
- 1: 用户数据
- 2: 日志数据
- 3: 交易数据
- 4: 缓存数据
- 5: 配置数据

### 清理类型 (cleanup_type)
- 1: 清空表
- 2: 删除所有
- 3: 按时间删除
- 4: 按用户删除
- 5: 按条件删除

### 计划类型 (plan_type)
- 1: 全量清理
- 2: 模块清理
- 3: 分类清理
- 4: 自定义清理
- 5: 混合清理

### 备份类型 (backup_type)
- 1: SQL
- 2: JSON
- 3: CSV

### 压缩类型 (compression_type)
- 1: none
- 2: gzip
- 3: zip

## 注意事项

1. Seeder 数据仅用于开发和测试环境
2. 生产环境请谨慎使用 Seeder
3. 运行 Seeder 前请备份数据库
4. 可以根据实际需要修改 Seeder 内容