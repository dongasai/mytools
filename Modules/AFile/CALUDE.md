# AFile 模块核心逻辑

## 概述
AFile 模块是 项目的文件管理核心模块，提供多存储配置管理和文件生命周期追踪能力。

## 核心逻辑（五句话）

1. **磁盘配置内部管理** - **最重要原则**：AFile 模块的 disk 配置完全由内部 StorageConfigService 管理，外部模块调用任何方法时**绝对不允许传入 disk 参数**。disk 通过 getDefaultDisk() 自动获取，确保存储策略统一可控。

2. **多存储配置管理** - StorageConfigService 动态管理多环境多驱动（local/S3/OSS）存储配置，配置缓存注册到 Laravel filesystem，支持配置变更历史追踪和连接测试。

3. **文件生命周期追踪** - 文件/图片有三状态流转：`normal`(上传初始) → `linked`(业务关联) → `dangling`(悬空待清理)，通过 `markFileAsUsed` 标记使用，事件驱动状态变更。

4. **统一文件服务** - FileService 提供上传/下载/删除/URL获取，底层 FileLogic 处理具体逻辑，UploadService/ImgService/TemporaryService 分工处理不同文件类型场景。

5. **事件驱动架构** - 上传/删除/使用标记事件触发跨模块监听（FileUploadedEvent、FileDeletedEvent、MarkFileUsedEvent），实现业务模块解耦协作。

## 核心组件

### Services 层
- **StorageConfigService**: 存储配置管理（创建/更新/删除/缓存/历史/测试）
- **FileService**: 文件服务门面（上传/下载/删除/标记使用）
- **UploadService**: 上传处理
- **ImgService**: 图片处理
- **TemporaryService**: 临时文件处理

### Logics 层
- **FileLogic**: 文件操作核心逻辑
- **DirLogic**: 目录管理逻辑
- **FileBaseLogic**: 文件基础逻辑

### Models 层
- **FileFile**: 文件模型（status: normal/linked/dangling）
- **FileImg**: 图片模型（支持私有/公开）
- **FileStorageConfig**: 存储配置模型
- **FileStorageConfigHistory**: 配置变更历史

### Events 层
- FileUploadedEvent / FileDeletedEvent
- ImageUploadedEvent / ImageDeletedEvent
- MarkFileUsedEvent / MarkImageUsedEvent

## 数据库设计

### 核心表
- `file_files`: 文件表（三状态追踪）
- `file_imgs`: 图片表（含私有标识）
- `file_storage_configs`: 存储配置表（多环境多驱动）
- `file_storage_config_histories`: 配置变更历史表

### 状态索引
- `idx_status_created_at`: 状态+创建时间组合索引
- `idx_status_dangling_at`: 状态+悬空时间组合索引

## 架构特点

1. **磁盘配置封闭管理**: AFile 模块 disk 配置完全内部化，外部模块无需关心存储细节，通过 getDefaultDisk() 自动获取，确保存储策略统一可控
2. **配置动态化**: 存储配置无需修改 config/filesystems.php，通过数据库动态管理
3. **状态可追踪**: 文件从上传到关联使用到悬空清理，完整生命周期追踪
4. **事件解耦**: 文件操作通过事件通知其他模块，实现业务解耦
5. **分层清晰**: Service(业务协调) → Logic(具体逻辑) → Model(数据层) 清晰分层

## 使用示例

```php
// === ModuleFileService 跨模块调用（推荐）===

// 1. 上传文件并标记使用
$file = ModuleFileService::uploadFile($uploadedFile, $userId, 'NovelAi_book_cover', $bookId);
ModuleFileService::markFileAsUsed($file->id); // normal → linked

// 2. 上传图片
$image = ModuleFileService::uploadImage($uploadedImage, $userId, true, 'NovelAi_book_cover', $bookId); // 私有图片
$imageUrl = ModuleFileService::getImageUrl($image->id, true);

// 3. 从已存在的路径创建图片记录（AI生成图片）
// 注意：不需要传入 disk 参数！disk 由 AFile 模块内部自动管理
$image = ModuleFileService::uploadImageForPath($storagePath, $userId, false, 'NovelAi_book_cover', $bookId);

// 4. 从已存在的路径创建文件记录（生成文件）
// 注意：不需要传入 disk 参数！disk 由 AFile 模块内部自动管理
$file = ModuleFileService::uploadFileForPath($storagePath, $userId, 'FeatureAi_report_pdf', $reportId, 'report.pdf');

// === FileService 内部调用 ===

// 上传文件
$fileService = new FileService();
$file = $fileService->uploadFile($uploadedFile, $userId);
$fileService->markFileAsUsed($file->id);

// 管理存储配置
$storageService = new StorageConfigService();
$storageService->createDisk('oss-prod', 'oss', $ossConfig, '生产OSS', true);
$storageService->testConnectionById($configId); // 测试连接

// === 禁止的错误用法 ===

// ❌ 错误：外部模块不允许传入 disk 参数
// ModuleFileService::uploadImageForPath($path, 'my-disk', $userId); // 禁止！

// ✅ 正确：让 AFile 模块自动管理 disk
// ModuleFileService::uploadImageForPath($path, $userId);
```

---
文档创建时间: 2026-05-28
文档更新时间: 2026-05-28 (增加磁盘配置封闭管理原则，新增 uploadImageForPath/uploadFileForPath 方法)