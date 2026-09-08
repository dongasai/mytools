# AClean 模块

- 模块名: AClean（数据清理模块）
- 表前缀: `cleanup_`
- 路由前缀: `/admin/aclean-admin`
- 命令前缀: `aclean:`

## 模块简介

AClean 模块提供数据清理功能，支持清理计划管理和安全的数据清理机制。

### 核心功能

- **清理计划管理**: 5种清理类型、5种数据分类、灵活表选择
- **安全机制**: 自动备份、预览模式、多重确认、SHA256 验证
- **批量处理**: 支持大数据量的分批处理
- **日志记录**: 详细的执行日志和统计信息
- **命令行工具**: 支持命令行执行扫描、清理、验证

## 命令工具

```bash
php artisan aclean:scan-models          # 扫描Model生成清理配置
php artisan aclean:data                 # 数据清理命令
php artisan aclean:test-model           # 测试Model清理
php artisan aclean:validate-model       # 验证Model清理配置
php artisan aclean:insert-admin-menu    # 插入后台菜单
```

## 目录结构

```
Modules/AClean/
├── Commands/           # 命令行工具
├── DcatAdmin/          # 超管后台控制器
│   └── Controllers/    # 6个控制器
├── Database/           # 迁移/Seeder
├── Dtos/               # 数据传输对象
├── Enums/              # 枚举定义
├── Events/             # 事件类
├── Helpers/            # 帮助类
├── Listeners/          # 事件监听器
├── Logics/             # 逻辑层
├── Models/             # 数据模型（9个）
├── Providers/          # 服务提供者
├── QueueJobs/          # 队列任务
├── Rules/              # 验证规则
├── Services/           # 服务层
├── config/             # 配置文件
├── resources/          # 视图/资源
└── routes/             # 路由定义
```

## 数据表

| 表名 | 说明 |
|------|------|
| `cleanup_configs` | 清理配置表 |
| `cleanup_plans` | 清理计划表 |
| `cleanup_plan_contents` | 计划内容表 |
| `cleanup_tasks` | 清理任务表 |
| `cleanup_backups` | 清理前备份记录表 |
| `cleanup_backup_files` | 备份文件表 |
| `cleanup_logs` | 清理日志表 |
| `cleanup_table_stats` | 表统计信息表 |
| `cleanup_sql_backups` | SQL备份表 |

## 后台菜单

- 父菜单ID: `340000`
- 子菜单:
  - `341000` - 清理配置管理
  - `342000` - 清理计划管理
  - `343000` - 计划内容管理
  - `344000` - 清理任务管理
  - `345000` - 清理日志管理
  - `346000` - 清理统计管理
