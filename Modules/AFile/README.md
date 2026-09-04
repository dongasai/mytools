# 文件模块（底层模块）

## 模块概述

文件模块是一个用于处理文件和图片上传、存储和访问的基础模块，供其他模块使用，提提供外部服务。
该模块提供了文件和图片的上传、存储、访问和管理功能，支持公共和私有文件的区分，以及文件与其他模块实体的关联。

## 目录结构

```
Modules/AFile/
├── Api/                            # API入口（RESTful）
│   └── Controllers/                # API控制器
│       ├── FileApiController.php   # 文件API控制器
│       └── ImageApiController.php  # 图片API控制器
├── DcatAdmin/                      # DcatAdmin后台入口
│   ├── Controllers/                # 后台控制器
│   │   ├── FileController.php      # 文件管理控制器
│   │   ├── ImageController.php     # 图片管理控制器
│   │   ├── FileTemplateController.php # 文件模板控制器
│   │   └── StorageConfigController.php # 存储配置控制器
│   ├── Helpers/                    # 辅助类
│   └── Repositories/               # 数据仓库
├── Casts/                          # 模型属性转换器
├── config/                         # 模块配置文件
│   ├── admin_menu.php              # Admin菜单配置
│   └── StorageConfig.php           # 存储配置类
├── Database/                       # 数据库相关
│   ├── Factories/                  # 模型工厂
│   ├── Migrations/                 # 数据库迁移
│   ├── Seeders/                    # 数据种子
│   └── GenerateSql/                # 自动生成的SQL文件
├── Docs/                           # 模块文档
│   ├── 数据库设计.md               # 数据库设计文档
│   └── 服务使用说明.md             # 服务使用说明文档
├── Dtos/                           # 数据传输对象
├── Enums/                          # 枚举类
├── Events/                         # 事件
├── Exceptions/                     # 异常处理
├── Listeners/                      # 事件监听器
├── Logics/                         # 业务逻辑层（静态方法）
│   ├── DirLogic.php                # 目录逻辑
│   ├── FileBaseLogic.php           # 文件基础逻辑
│   └── FileLogic.php               # 文件逻辑
├── Models/                         # 数据模型
│   ├── FileFile.php                # 文件模型
│   ├── FileImg.php                 # 图片文件模型
│   ├── FileStorageConfig.php       # 存储配置模型
│   ├── FileStorageConfigHistory.php # 存储配置历史模型
│   └── FileTemplate.php            # 文件模板模型
├── Providers/                      # 服务提供者
│   └── FileServiceProvider.php     # 文件服务提供者
├── QueueJobs/                      # 队列任务
├── Rules/                          # 自定义验证规则
│   ├── UserFileImgRule.php         # 用户图片验证规则
│   └── UserFileImgValidatorLegacy.php # 旧版验证器（兼容）
├── Services/                       # 服务层
│   ├── FileService.php             # 文件服务
│   ├── ImgService.php              # 图片服务
│   ├── StorageConfigService.php    # 存储配置服务
│   ├── TemporaryService.php        # 临时文件服务
│   └── UploadService.php           # 上传服务
├── Tests/                          # 测试文件
│   ├── Feature/                    # 功能测试
│   └── Unit/                       # 单元测试
├── routes/                         # 路由定义
│   ├── api.php                     # API路由
│   └── admin.php                   # Admin后台路由
├── module.json                     # 模块配置
└── README.md                       # 模块说明
```

## 数据库表结构

### 文件表 (file_files)

存储普通文件信息。

| 字段名 | 类型 | 说明 |
|-------|------|------|
| id | bigint | 主键 |
| path | varchar(1000) | 存储目录 |
| re_type | varchar(100) | 关联类型 |
| re_id | int | 关联ID |
| o_name | varchar(200) | 原文件名 |
| fsize | bigint | 文件大小 |
| type1 | varchar(100) | 文件类型 |
| updated_at | timestamp | 更新时间 |
| created_at | timestamp | 创建时间 |
| deleted_at | timestamp | 删除时间 |

### 图片表 (file_imgs)

存储图片文件信息。

| 字段名 | 类型 | 说明 |
|-------|------|------|
| id | bigint | 主键 |
| storage_disk | varchar(100) | 存储磁盘 |
| path | varchar(1000) | 存储路径 |
| user_id | bigint | 用户ID |
| admin_id | bigint | 管理员ID |
| re_type | varchar(100) | 关联类型 |
| re_id | int | 关联ID |
| o_name | varchar(200) | 原文件名 |
| fsize | bigint | 文件大小 |
| width | int | 图片宽度 |
| height | varchar(1000) | 图片高度 |
| type1 | varchar(100) | 图片类型 |
| private | tinyint | 是否私有，0:公共，1:私有 |
| updated_at | timestamp | 更新时间 |
| created_at | timestamp | 创建时间 |
| deleted_at | timestamp | 删除时间 |

### 文件模板表 (file_template)

存储文件模板信息。

| 字段名 | 类型 | 说明 |
|-------|------|------|
| id | bigint | 主键 |
| unid | varchar(500) | 标识 |
| file_id | int | 文件ID |
| title | varchar(500) | 模板标题 |
| desc | varchar(100) | 描述 |
| status | varchar(1) | 状态 |
| group | varchar(100) | 分组 |
| updated_at | timestamp | 更新时间 |
| created_at | timestamp | 创建时间 |
| deleted_at | timestamp | 删除时间 |

## 主要功能

### 文件上传

模块提供了文件和图片的上传功能，支持将文件上传到指定的存储磁盘，并记录文件信息到数据库。

### 图片处理

支持图片的上传、转换和处理，包括图片格式转换、缩放等功能。

### 文件访问

提供了获取文件和图片URL的方法，支持公共和私有文件的访问控制。

### 临时文件处理

支持临时文件的创建、存储和访问，用于临时文件处理场景。

### 文件模板

支持文件模板的管理，可用于生成基于模板的文件。

## 使用示例

### 上传用户图片

```php
// 创建用户上传处理器
$uploader = new \Modules\AFile\Upload4User($userId);

// 上传图片
$fileImg = $uploader->uploadImg($request->file('image'));

// 获取图片URL
$imageUrl = $fileImg->getUrl();
```

### 获取图片URL

```php
// 通过ID获取图片URL
$imageUrl = \Modules\AFile\Img::getPicUrl4Id($imageId);

// 通过路径获取管理后台图片URL
$imageUrl = \Modules\AFile\Img::getAdminPicUrl($imagePath);
```

### 临时文件处理

```php
// 保存临时文件
$path = \Modules\AFile\Temporary::save('jpg', $fileContent);

// 获取临时文件下载URL
$downloadUrl = \Modules\AFile\Temporary::getDownUrl($path);
```

### 文件存在性检查

```php
// 检查文件是否存在
$exists = \Modules\AFile\File::fileExists($filePath);
```

## 配置

文件模块使用Laravel的文件系统配置，主要配置项包括：

- `FILESYSTEM_DISK`: 默认存储磁盘兜底配置（数据库无默认盘配置时使用），默认为'local'
- 磁盘配置主要由数据库 `file_storage_configs` 表管理：
  - `is_default=true` 的记录作为默认存储磁盘
  - `is_temp=true` 的记录作为临时文件存储磁盘
  - 临时盘未配置时回退默认盘

这些配置可以在.env文件中设置（仅作为数据库未配置时的兜底）。
