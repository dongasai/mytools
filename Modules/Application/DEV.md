# Application 模块开发日志

## 模块概述

Application 模块是系统核心模块，负责管理系统配置、钩子、作业、定时任务等核心功能。

## 最近工作记录

### 2025-11-29 修复 Job 模型继承问题

- 修复了 `Job` 模型的继承问题，将 `use DLaravel\ModelCore;` 改为 `use Illuminate\Database\Eloquent\Model;`
- 将 `class Job extends ModelCore` 改为 `class Job extends Model`
- 解决了 IDE 报错：`[intelephense] Undefined type 'DLaravel\ModelCore'. (P1009)`
- 确保模型继承 Laravel 原生 Model 类，提高代码兼容性和稳定性
- 保持了所有原有的模型属性和方法不变

### 2025-11-29 创建 failed_jobs 表迁移文件

- 创建了 `failed_jobs` 表的迁移文件：`2025_11_29_140402_create_failed_jobs_table.php`
  - 表结构包含：id, uuid, connection, queue, payload, exception, failed_at
  - uuid 字段设置了唯一约束，确保失败任务不会重复记录
  - 使用 utf8mb4_unicode_ci 字符集，支持完整的 Unicode 字符
  - 添加了表存在性检查，提高迁移的健壮性
- `FailedJob` 模型已存在且与表结构匹配
  - 继承自 `DLaravel\ModelCore`，包含了所有必要的字段
  - 添加了多个访问器，提供便捷的数据访问方式
  - 将 payload 转换为数组类型，方便操作

### 2025-11-29 创建 continuous_times 表迁移和种子文件

- 创建了 `continuous_times` 表的迁移文件：`2025_11_29_135955_create_continuous_times_table.php`
  - 表结构包含：id, user_id, stype, sid, number, last_time, created_at, updated_at, deleted_at, diff
  - 添加了索引：user_id, [stype, sid], last_time，提高查询效率
  - 设置了表注释：连续次数判定
  - 添加了表存在性检查，提高迁移的健壮性
- 创建了 `ContinuousTimesSeeder` 种子文件，填充连续次数判定的示例数据
  - 包含了多种连续类型：sign（签到）、read（阅读）、login（登录）、check_in（打卡）
  - 模拟了真实的连续行为和时间差计算
- `ContinuousTimes` 模型已存在且与表结构匹配

### 2025-11-29 检查管理员操作日志表

- 发现 `admin_action_logs` 表的迁移文件已存在于 DcatAdmin 模块中：`2025_11_29_182458_create_admin_action_logs_table.php`
- 表结构包含：id, type1, unid, admin_id, object_class, url, before, after, status, p1, created_at, updated_at
- `AdminActionlog` 模型已存在于 `Modules\DcatAdmin\Models` 命名空间下
- 表设计合理，包含必要的索引和外键约束

### 2025-11-29 创建 sys_configs 表迁移和种子文件

- 创建了 `sys_configs` 表的迁移文件：`2025_11_29_135213_create_sys_configs_table.php`
  - 添加了表存在性检查，提高迁移的健壮性
- 创建了 `SysConfigSeeder` 种子文件，填充系统配置的初始数据
- `SysConfig` 模型已存在且与表结构匹配
- `ConfigService` 服务类已存在，提供配置的读取和缓存功能
- `CONFIG_TYPE` 枚举类已存在，定义了配置的类型

### 2025-11-XX 之前的工作

（此处保留之前的工作记录，只保留最近的10条）

## 技术栈

- PHP 8.3
- Laravel 12
- 模块系统：nwidart/laravel-modules

## 目录结构

```
Application/
├── Commands/                  # 控制台命令
├── Console/                   # 控制台相关
├── Databases/                 # 数据库相关
│   ├── GenerateSql/          # 自动生成的SQL文件
│   ├── Migrations/           # 数据库迁移
│   └── Seeders/              # 数据填充
├── Dtos/                      # 数据传输对象
├── Enums/                     # 枚举类型
├── Events/                    # 事件定义
├── Hooks/                     # 钩子定义
├── Listeners/                 # 事件监听器
├── Models/                    # Eloquent模型
├── Providers/                 # 服务提供者
├── Services/                  # 服务层
└── Tests/                     # 测试文件
```

## 核心功能

1. 系统配置管理 (`SysConfig`)
2. 管理员操作日志 (`AdminActionlog`)
3. 连续次数判定 (`ContinuousTimes`)
4. 失败队列任务 (`FailedJob`)
5. 队列任务运行记录 (`JobRun`)
6. 钩子系统 (`Hook`)
7. 作业管理 (`Job`)
8. 系统日志 (`SystemLog`)
9. 定时任务调度

## 开发规范

1. 遵循 PSR-4 自动加载标准
2. 使用 PHPDoc 注释规范
3. 服务层用于协调不同组件，处理复杂业务流程
4. 模型层负责数据结构和业务方法
5. 事件系统用于解耦组件间的依赖
