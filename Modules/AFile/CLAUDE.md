# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

---

## 模块概述

AFile 是能碳管理 EMS 的文件管理核心模块，提供文件和图片的上传、下载、存储配置、临时文件清理等完整功能。

**核心特性**：
- 文件/图片统一管理（支持私有/公开访问）
- 多存储配置动态切换（本地/S3/OSS 等）
- 文件生命周期状态管理（normal → linked → dangling）
- 悬空文件追踪与清理机制
- 临时文件自动清理
- 模块间文件管理服务

---

## 核心架构

### 数据表

| 表名 | 说明 | 关键字段 |
|------|------|---------|
| `file_files` | 文件记录表 | `storage_disk`, `user_id`, `re_type`, `re_id`, `status`, `used_at`, `dangling_at` |
| `file_imgs` | 图片记录表 | `storage_disk`, `user_id`, `admin_id`, `private`, `width`, `height`, `status` |
| `file_storage_configs` | 存储配置表 | `name`, `driver`, `is_active`, `config` (JSON) |
| `file_storage_config_histories` | 配置变更历史 | `config_id`, `old_config`, `new_config` |
| `file_template` | 模板文件表 | `name`, `type`, `path` |

### 文件状态生命周期

```
上传 → normal（正常，未关联业务）
     ↓
关联业务 → linked（已关联，正在使用）
     ↓
解除关联 → dangling（悬空，待清理）
     ↓
定时清理 → 删除
```

**状态字段**：
- `normal`: 正常状态，刚上传未关联业务
- `linked`: 已关联业务，正在使用
- `dangling`: 悬空状态，已解除关联等待清理
- `used_at`: 文件被标记为已使用的时间
- `dangling_at`: 文件进入悬空状态的时间

---

## 核心类与使用方法

### 静态辅助类

**`Modules\AFile\File`** - 文件辅助类
```php
// 检查文件是否存在
File::fileExists(string $path, ?string $disk = null): bool

// 通过ID获取文件URL
File::getUrl4Id(int $id): string

// 通过路径获取文件URL
File::getUrl4Path(string $path, ?string $disk = null): string

// 获取文件内容
File::getContent(string $path, ?string $disk = null): ?string

// 删除文件
File::delete4Id(int $id): bool
```

**`Modules\AFile\Img`** - 图片辅助类
```php
// 通过ID获取图片URL（支持私有图片）
Img::getPicUrl4Id(int $id, bool $private = false): string

// 获取管理后台图片URL
Img::getAdminPicUrl(string $path): string

// 检查后台图片是否存在
Img::hasAdminPic(string $path): bool

// 保存后台图片
Img::saveAdminPic(string $path, $data): bool
```

### FileService（核心服务）

**上传文件**：
```php
use Modules\AFile\Services\FileService;

// 上传文件
$fileModel = FileService::uploadFile(
    UploadedFile $file,
    int $userId,
    string $reType = '',    // 关联类型（如 'enterprise', 'product'）
    int $reId = 0           // 关联ID
);

// 上传图片（支持私有/公开）
$imgModel = FileService::uploadImage(
    UploadedFile $file,
    int $userId,
    bool $private = false,  // 是否私有图片
    string $reType = '',
    int $reId = 0
);
```

**标记文件为已使用**：
```php
// 单个文件标记
FileService::markFileAsUsed(int $fileId): bool

// 批量标记
FileService::batchMarkAsUsed(array $fileIds): bool

// 标记图片
FileService::markImageAsUsed(int $imageId): bool
```

**取消文件关联**：
```php
// 取消关联但不删除文件（标记为 dangling）
FileService::unlinkFile(int $fileId, bool $markDangling = true): bool

// 取消关联并删除文件
FileService::unlinkAndDeleteFile(int $fileId): bool

// 批量取消关联
FileService::batchUnlinkFiles(array $fileIds, bool $markDangling = true): bool
FileService::batchUnlinkAndDeleteFiles(array $fileIds): bool
```

**从已存在路径创建文件记录**：
```php
// 用于 AI 生成图片、导出文件等场景
$imgModel = FileService::createImageFromPath(
    string $path,           // 存储路径
    int $userId,
    string $reType = '',
    int $reId = 0,
    bool $private = false
);

$fileModel = FileService::createFileFromPath(
    string $path,
    int $userId,
    string $reType = '',
    int $reId = 0,
    string $originalName = ''
);
```

**临时文件处理**：
```php
// 保存临时文件（公开访问）
$publicUrl = FileService::saveTempFilePublic(string $ext, string $content): string

// 保存临时文件（内部访问）
$tempPath = FileService::saveTempFile(string $ext, string $content): string

// 获取临时文件URL
$tempUrl = FileService::getTempFileUrl(string $path): string
```

### ModuleFileService（模块文件管理）

为其他模块提供专属文件管理服务，支持模块级存储配置。

```php
use Modules\AFile\Services\ModuleFileService;

// 为模块上传文件
$fileModel = ModuleFileService::uploadFile(
    string $module,         // 模块名（如 'enterprise', 'nt_energy'）
    UploadedFile $file,
    int $userId,
    string $reType = '',
    int $reId = 0
);

// 获取模块存储磁盘
$disk = ModuleFileService::getModuleDisk(string $module): string

// 获取模块存储配置
$config = ModuleFileService::getModuleStorageConfig(string $module): array
```

---

## 存储配置管理

### StorageConfigService

管理动态存储配置，支持运行时切换存储磁盘。

```php
use Modules\AFile\Services\StorageConfigService;

// 获取当前激活的存储配置
$config = StorageConfigService::getActiveConfig(): ?FileStorageConfig

// 获取指定磁盘配置
$config = StorageConfigService::getConfig(string $diskName): ?FileStorageConfig

// 切换存储配置
StorageConfigService::switchStorage(string $diskName): bool

// 创建存储配置
$config = StorageConfigService::createConfig(array $data): FileStorageConfig
```

### 存储配置检查

**FileStorageConfigCheckService** - 存储配置检查服务
- 检查存储配置是否有效
- 验证存储磁盘是否可写
- 检测存储配置变更

**相关文档**：
- `Modules/AFile/Docs/配置管理.md`
- `Modules/AFile/Docs/file_storage_config_check.md`
- `Modules/AFile/Docs/storage_config_fix.md`

---

## 临时文件清理

### CleanTempFilesCommand

**命令**：
```bash
php artisan afile:clean-temp {--days=3}
```

**说明**：
- 清理超过指定天数的临时文件
- 默认保留 3 天内的文件
- 自动清理空目录
- 输出详细统计信息

**临时文件目录结构**：
```
public/storage/temp/
├── 202608/          # 年月
│   ├── 30/          # 日期
│   │   ├── file1.xlsx
│   │   └── file2.pdf
│   └── 29/
└── 202607/
```

**相关文档**：
- `Modules/AFile/Docs/临时文件清理功能.md`

---

## 事件系统

AFile 模块提供事件钩子，允许其他模块响应文件操作。

**事件类**（`Modules\AFile\Events`）：
- `MarkFileUsedEvent` - 文件被标记为已使用
- `MarkImageUsedEvent` - 图片被标记为已使用
- `FileDeletedEvent` - 文件被删除
- `ImageDeletedEvent` - 图片被删除

**监听器示例**：
```php
use Modules\AFile\Events\MarkFileUsedEvent;

class MyFileListener
{
    public function handle(MarkFileUsedEvent $event)
    {
        $fileId = $event->fileId;
        // 执行业务逻辑
    }
}
```

**相关文档**：
- `Modules/AFile/Docs/事件系统.md`

---

## 与其他模块集成

### 标准集成流程

1. **上传文件时指定关联类型**：
```php
$fileModel = FileService::uploadFile(
    $file,
    $userId,
    'enterprise',  // 关联类型
    $enterpriseId  // 关联ID
);
```

2. **业务数据保存后标记文件为已使用**：
```php
DB::transaction(function () use ($fileId, $enterpriseId) {
    // 保存业务数据
    $enterprise = Enterprise::create($data);

    // 标记文件为已使用
    FileService::markFileAsUsed($fileId);
});
```

3. **删除业务数据时取消文件关联**：
```php
DB::transaction(function () use ($enterpriseId, $fileId) {
    // 删除业务数据
    Enterprise::destroy($enterpriseId);

    // 取消文件关联并删除
    FileService::unlinkAndDeleteFile($fileId);
});
```

### 模块专属存储配置

在 `Modules/{Module}/config/storage.php` 中配置：
```php
return [
    'disk' => 'enterprise_storage',  // 模块专属存储磁盘
    'path_prefix' => 'enterprise/',  // 路径前缀
];
```

**相关文档**：
- `Modules/AFile/Docs/与其他模块集成.md`
- `Modules/AFile/Docs/ModuleFileService使用指南.md`

---

## API 路由

### 文件 API（`/api/file`）

- `POST /api/file/upload` - 上传文件
- `GET /api/file/{id}/download` - 下载文件
- `GET /api/file/{id}/url` - 获取文件URL
- `DELETE /api/file/{id}` - 删除文件

### 图片 API（`/api/file/image`）

- `POST /api/file/image/upload` - 上传图片
- `GET /api/file/image/{id}/download` - 下载图片
- `GET /api/file/image/{id}/download/private` - 下载私有图片（需认证）
- `DELETE /api/file/image/{id}` - 删除图片

**API 文档**：`Modules/AFile/Docs/API文档.md`

---

## Dcat Admin 后台

### 路由（`/admin`）

- `/admin/files` - 文件管理
- `/admin/images` - 图片管理
- `/admin/storage-configs` - 存储配置管理

**Actions**：
- `CopyStorageConfigAction` - 复制存储配置
- `FixStorageConfigAction` - 修复存储配置

---

## 常用命令

```bash
# 清理临时文件
php artisan afile:clean-temp --days=3

# 测试存储配置
php artisan afile:test-storage

# 运行迁移（仅开发环境）
php artisan module:migrate AFile
```

---

## 开发规范

### 必须遵守

- **禁止跨模块调用 Model**：其他模块通过 `FileService` 或 `ModuleFileService` 访问
- **文件关联必须使用 `re_type` 和 `re_id`**：避免悬空文件
- **上传后必须标记为已使用**：`markFileAsUsed()` 或 `batchMarkAsUsed()`
- **删除业务数据时取消文件关联**：`unlinkAndDeleteFile()` 或 `batchUnlinkAndDeleteFiles()`
- **优先使用静态方法**：Service 层方法均为静态方法
- **禁止在 Service 中读取 HTTP/session**：参数必须显性传入

### 分层规范

```
Models (FileFile, FileImg, FileStorageConfig)
    ↓
Services (FileService, ImgService, StorageConfigService)
    ↓
Logics (FileLogic, StorageConfig)
    ↓
Controllers (DcatAdmin/Controllers, Api/Controllers)
```

---

## 文档资源

**核心文档**（`Modules/AFile/Docs/`）：
- `服务使用说明.md` - FileService 完整使用指南
- `数据库设计.md` - 数据表设计详解
- `文件业务管理逻辑梳理.md` - 文件生命周期管理
- `配置管理.md` - 存储配置管理详解
- `事件系统.md` - 事件与监听器
- `与其他模块集成.md` - 模块集成最佳实践
- `ModuleFileService使用指南.md` - 模块专属存储配置
- `API文档.md` - REST API 文档

---

## 关键设计模式

### 悬空文件追踪机制

**问题**：文件上传后未关联业务，或关联被取消后未及时清理，导致存储空间浪费。

**解决方案**：
1. 文件状态追踪（`status`, `used_at`, `dangling_at`）
2. 取消关联时标记为 `dangling` 状态
3. 定时任务清理悬空文件（可扩展）

**最佳实践**：
```php
// ✅ 正确：删除业务时取消文件关联
FileService::unlinkAndDeleteFile($fileId);

// ❌ 错误：只删除业务数据，不处理文件
Enterprise::destroy($enterpriseId);
```

### 多存储配置动态切换

**应用场景**：
- 开发环境使用本地存储
- 生产环境使用 OSS/S3
- 不同模块使用不同存储

**实现**：
- `file_storage_configs` 表存储配置
- `StorageConfigService` 动态读取配置
- `ModuleFileService` 提供模块级存储

---

## 扩展建议

详见：`Modules/AFile/Docs/扩展建议.md`