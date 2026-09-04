# ABase 模块

> 核心基础模块

## 模块概述

ABase是一个核心基础模块，为其他模块提供基础功能、基类、工具类和服务。该模块按照标准的Laravel模块结构进行组织，便于维护和扩展。

## 功能列表

* **配置表备份服务** - 提供配置数据的备份和恢复功能
* **枚举类型** - 提供常用的枚举定义，如备份模式、压缩类型等
* **工具类** - 提供配置表枚举辅助工具
* **Console命令** - 提供项目树生成、配置备份、模型注释生成等工具命令
* **基础服务提供者** - 为其他模块提供基础支持

## 目录结构

```
Modules/ABase/
├── Console/                    # 控制台命令
│   ├── GenerateAppTreeCommand.php      # 项目文件树生成命令
│   ├── GenerateConfigDbCommand.php     # 配置表备份命令
│   └── GenerateModelAnnotation.php     # 模型注释生成命令
├── Enums/                      # 枚举类
│   ├── ConfigDbBackupMode.php         # 配置备份模式
│   ├── ConfigDbCompressionType.php     # 压缩类型
│   ├── ConfigDbConditionType.php       # 条件类型
│   ├── ConfigDbNotificationChannel.php # 通知渠道
│   ├── ConfigDbType.php                # 配置类型
│   └── ConfigDbValidationRule.php      # 验证规则
├── Providers/                  # 服务提供者
│   ├── ABaseServiceProvider.php        # 主服务提供者
│   └── EventServiceProvider.php        # 事件服务提供者
├── Services/                   # 服务类
│   └── ConfigDbBackupService.php       # 配置备份服务
├── Support/                    # 支持类
│   ├── ConfigDbEnumHelper.php          # 枚举辅助工具
│   └── ServiceProvider.php             # 基础服务提供者
├── config/                     # 配置文件
├── database/                   # 数据库相关
├── resources/                  # 资源文件
└── tests/                      # 测试文件
```

## 可用命令

### 项目文件树生成
```bash
php artisan abase:generate-apptree
```
生成app目录的文件树并保存到app/tree.md，包含详细的统计信息。

### 配置表备份
```bash
php artisan abase:generate-configdb
```
生成配置表的数据库备份文件。

### 模型注释生成
```bash
php artisan abase:generate-model-annotation
```
自动生成Eloquent模型属性注释和表创建SQL。

## 使用方式

### 配置备份服务
```php
use Modules\ABase\Services\ConfigDbBackupService;

$backupService = app(ConfigDbBackupService::class);
$backupService->createBackup();
```

### 枚举使用
```php
use Modules\ABase\Enums\ConfigDbBackupMode;
use Modules\ABase\Enums\ConfigDbCompressionType;

$mode = ConfigDbBackupMode::FULL;
$compression = ConfigDbCompressionType::GZIP;
```

## 设计原则

* **核心定位** - 作为核心模块，提供基础功能，不涉及具体的业务逻辑
* **标准结构** - 遵循Laravel模块标准结构，便于维护
* **命名规范** - 使用统一的命名空间 `Modules\ABase`
* **模块化设计** - 功能明确分类，便于按需使用
* **可扩展性** - 提供基础类和服务，支持其他模块扩展

## 不包含的功能

- DcatAdmin相关内容
- Web界面输出
- Admin后台管理
- API接口开放
- 路由功能

> 作为核心模块，专注于提供基础功能、工具类和服务，不涉及具体的业务逻辑实现。