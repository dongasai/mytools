# AClean 模块开发记录

## 2026-08-23

### BackupAndClean → AClean 模块重构

**功能概述**: 将 BackupAndClean 模块重定义为"AClean"数据清理模块，移除独立备份功能，专注于数据清理

**重构内容**:
- ✅ 模块目录: `Modules/BackupAndClean` → `Modules/AClean`
- ✅ module.json 更新（name/alias/description/providers）
- ✅ 全局命名空间: `Modules\BackupAndClean` → `Modules\AClean`
- ✅ Provider 类重命名: `BackupAndCleanServiceProvider` → `ACleanServiceProvider`
- ✅ 配置文件: `backupandclean.php` → `aclean.php`
- ✅ 路由前缀: `backup-clean-admin` → `aclean-admin`
- ✅ 菜单ID更新: `33****` → `34****`
- ✅ Command 签名: `backupandclean:` → `aclean:`
- ✅ 文档更新（README.md/DEV.md）

**保留功能**:
- 清理计划管理（cleanup_plans）
- 清理配置管理（cleanup_configs）
- 清理任务管理（cleanup_tasks）
- 清理日志管理（cleanup_logs）
- 清理前自动备份（cleanup_backups, cleanup_backup_files, cleanup_sql_backups）

**移除功能**:
- 独立备份计划管理
- 备份批次跟踪（cleanup_backup_run_batches）
- 备份表进度（cleanup_backup_run_tables）
- 备份分片（cleanup_backup_run_table_splits）
- 备份日志（cleanup_backup_run_logs）

---

## 历史开发记录

## 2026-08-02

### Cleanup → BackupAndClean 模块重定义重构

**功能概述**: 将 Cleanup 模块重定义为"备份与清理"模块，扩展独立备份功能

**Phase 0: Bug 修复**
- ✅ 修复 BackupLogic 中 `status` → `backup_status` 列名错误
- ✅ 修复 `BACKUP_STATUS::PENDING`/`RUNNING` → `IN_PROGRESS` 枚举错误

**Phase 1: 基础重命名**
- ✅ 模块目录: `Modules/Cleanup` → `../ABackup`
- ✅ module.json 更新（name/alias/description/providers）
- ✅ 全局命名空间: `Modules\Cleanup` → `Modules\BackupAndClean`（101个PHP文件）
- ✅ Provider 类重命名: `CleanupServiceProvider` → `BackupAndCleanServiceProvider`
- ✅ 配置文件: `cleanup.php` → `backupandclean.php`
- ✅ 路由前缀: `cleanup-admin` → `backup-clean-admin`

**Phase 2: 数据库变更**
- ✅ cleanup_backups 表 plan_id 改 nullable + 新增 source_type 字段
- ✅ 新增 cleanup_backup_plans 表（独立备份计划）

**Phase 3: 新增独立备份功能**
- ✅ 枚举文件拆分（BACKUP_STATUS/COMPRESSION_TYPE 独立文件）
- ✅ 新增 BACKUP_SOURCE_TYPE 枚举
- ✅ 新增 CleanupBackupPlan 模型
- ✅ 更新 CleanupBackup 模型（backupPlan关联/source_type）
- ✅ 扩展 BackupLogic 支持独立备份
- ✅ CleanupService → BackupAndCleanService

**Phase 4: 后台界面**
- ✅ 新增 BackupPlanController + Repository
- ✅ 路由更新（独立备份计划资源路由）
- ✅ 菜单配置更新（新增独立备份计划菜单项）
- ✅ Actions 路由引用更新

**Phase 5: 收尾**
- ✅ Command 签名: `cleanup:` → `backupandclean:`
- ✅ QueueJob/Event/Listener 命名空间确认
- ✅ Helpers/Rules/Dtos/Views 硬编码字符串更新
- ✅ 文档更新（Modules/CLAUDE.md/README.md/DEV.md）
- ✅ 全局残留搜索 + 缓存清理

## 历史开发记录

## 2025-12-07

### 1. 按照 Demo5 模式重构 Cleanup 模块

**功能概述**: 根据 Demo5 模块的标准结构重构 Cleanup 模块，建立完整的分层架构

**实现内容**:
- ✅ 创建标准的目录结构（Dtos, Events, Listeners, QueueJobs, Rules, Tests 等）
- ✅ 重构 Providers 目录，添加 EventServiceProvider 和 HookServiceProvider
- ✅ 更新 CleanupServiceProvider 的命名空间和路径引用
- ✅ 更新 module.json 配置文件，注册所有服务提供者
- ✅ 修复迁移文件与 SQL 文件的不一致问题
- ✅ 成功执行数据库迁移，创建所有必要的表结构

**技术特点**:
- 遵循 Demo5 模块的最佳实践和代码规范
- 实现完整的分层架构（Models, Services, Logics, Enums 等）
- 建立标准的事件驱动架构
- 支持完整的迁移和数据库管理
- 提供可扩展的 Hook 系统

**目录结构**:
```
Modules/Cleanup/
├── Providers/                     # 服务提供者
│   ├── CleanupServiceProvider.php
│   ├── EventServiceProvider.php
│   └── HookServiceProvider.php
├── Commands/                      # Artisan 命令
├── Database/                      # 数据库相关
│   ├── Factories/                # 模型工厂
│   ├── Migrations/               # 迁移文件
│   └── Seeders/                  # 数据填充
├── Models/                        # Eloquent 模型
├── Services/                      # 服务层
├── Logics/                        # 逻辑层
├── Enums/                         # 枚举类
├── Dtos/                         # 数据传输对象
├── Events/                       # 事件类
├── Listeners/                     # 事件监听器
├── QueueJobs/                     # 队列任务
├── Rules/                        # 验证规则
├── Tests/                        # 测试文件
│   ├── Feature/                  # 功能测试
│   └── Unit/                     # 单元测试
├── Hooks/                        # Hook 系统
│   ├── Definitions/
│   ├── Handlers/
│   ├── Parameters/
│   └── Results/
├── resources/                     # 资源文件
│   ├── assets/                   # 静态资源
│   └── views/                    # 视图模板
└── config/                       # 配置文件
```

**数据库结构**:
- ✅ cleanup_plans（清理计划表）
- ✅ cleanup_configs（清理配置表）
- ✅ cleanup_tasks（清理任务表）
- ✅ cleanup_backups（备份记录表）
- ✅ cleanup_backup_files（备份文件表）
- ✅ cleanup_logs（清理日志表）
- ✅ cleanup_plan_contents（计划内容表）
- ✅ cleanup_sql_backups（SQL 备份表）
- ✅ cleanup_table_stats（表统计信息表）

**修复的问题**:
- ✅ 修复 cleanup_plans 表缺失 plan_type 和 target_selection 字段
- ✅ 添加缺失的索引 idx_plan_type
- ✅ 解决外键约束依赖关系问题
- ✅ 统一迁移文件与 SQL 文件的结构定义
