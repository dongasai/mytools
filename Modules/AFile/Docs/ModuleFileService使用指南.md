# ModuleFileService 使用指南

## 1. 概述

`ModuleFileService` 是专门为其他模块提供文件服务的静态方法类，简化跨模块对接。

### 特点
- **全静态方法**: 无需实例化，直接调用
- **异常捕获**: 所有方法都有异常处理，返回 null 或 false
- **日志记录**: 自动记录错误日志，便于排查问题
- **完整业务流程**: 提供文件全生命周期管理方法

---

## 2. 核心方法分类

### 2.1 上传类方法

| 方法 | 说明 | 推荐场景 |
|------|------|---------|
| `uploadFile()` | 上传文件 | 已知业务ID |
| `uploadImage()` | 上传图片 | 已知业务ID |
| `uploadFileAndMark()` | 上传文件并标记使用(一步完成) | ✅ **推荐使用** |
| `uploadImageAndMark()` | 上传图片并标记使用(一步完成) | ✅ **推荐使用** |

### 2.2 标记使用类方法

| 方法 | 说明 |
|------|------|
| `markFileAsUsed()` | 标记文件为已使用 |
| `markImageAsUsed()` | 标记图片为已使用 |
| `batchMarkFilesAsUsed()` | 批量标记文件 |
| `batchMarkImagesAsUsed()` | 批量标记图片 |

### 2.3 取消关联类方法

| 方法 | 说明 | 推荐场景 |
|------|------|---------|
| `unlinkFile()` | 取消文件关联(不删除文件) | 保留文件，标记悬空 |
| `unlinkImage()` | 取消图片关联(不删除图片) | 保留图片，标记悬空 |
| `unlinkAndDeleteFile()` | 取消关联并删除文件 | ✅ **彻底清理** |
| `unlinkAndDeleteImage()` | 取消关联并删除图片 | ✅ **彻底清理** |
| `batchUnlinkFiles()` | 批量取消文件关联 | 批量业务删除 |
| `batchUnlinkImages()` | 批量取消图片关联 | 批量业务删除 |
| `batchUnlinkAndDeleteFiles()` | 批量取消关联并删除文件 | ✅ **批量彻底清理** |
| `batchUnlinkAndDeleteImages()` | 批量取消关联并删除图片 | ✅ **批量彻底清理** |

### 2.4 删除类方法

| 方法 | 说明 |
|------|------|
| `deleteFile()` | 删除文件(物理删除) |
| `deleteImage()` | 删除图片(物理删除) |
| `batchDeleteFiles()` | 批量删除文件 |
| `batchDeleteImages()` | 批量删除图片 |

### 2.5 URL获取类方法

| 方法 | 说明 |
|------|------|
| `getFileUrl()` | 获取文件下载URL |
| `getImageUrl()` | 获取图片访问URL |
| `batchGetImageUrls()` | 批量获取图片URL [imageId => url] |
| `getFirstImageUrlByRe()` | 根据关联信息获取第一个图片URL |

### 2.6 查询类方法

| 方法 | 说明 |
|------|------|
| `getFile()` | 获取文件模型 |
| `getImage()` | 获取图片模型 |
| `getFilesByRe()` | 根据关联信息获取文件列表 |
| `getImagesByRe()` | 根据关联信息获取图片列表 |
| `getFirstFileByRe()` | 根据关联信息获取第一个文件 |
| `getFirstImageByRe()` | 根据关联信息获取第一个图片 |
| `fileExists()` | 检查文件是否存在 |
| `imageExists()` | 检查图片是否存在 |

### 2.7 临时文件类方法

| 方法 | 说明 |
|------|------|
| `saveTempFile()` | 保存临时文件 |
| `getTempFileUrl()` | 获取临时文件URL |

---

## 3. 使用示例

### 3.1 上传文件并关联(一步完成)

```php
use Modules\AFile\Services\ModuleFileService;

// 在 NovelAi 模块中创建章节封面
public function createChapter(Request $request)
{
    $chapter = Chapter::create([...]);
    
    // 上传封面图片并标记使用(一步完成)
    $coverImage = ModuleFileService::uploadImageAndMark(
        $request->file('cover'),
        $userId,
        false,  // 公开
        'novel_chapter_cover',
        $chapter->id
    );
    
    if ($coverImage) {
        $chapter->cover_image_id = $coverImage->id;
        $chapter->save();
    }
    
    return response()->json([...]);
}
```

### 3.2 两步法(上传后关联)

```php
use Modules\AFile\Services\ModuleFileService;

// 步骤1: 先上传文件(不关联)
$file = ModuleFileService::uploadFile(
    $request->file('attachment'),
    $userId
);

// 步骤2: 业务创建后关联并标记
$business = Business::create([...]);

if ($file) {
    // 更新文件关联信息
    $file->re_type = 'module_business_file';
    $file->re_id = $business->id;
    $file->save();
    
    // 标记为已使用
    ModuleFileService::markFileAsUsed($file->id);
}
```

### 3.3 批量上传图片

```php
use Modules\AFile\Services\ModuleFileService;

// 批量上传商品详情图
$product = Product::create([...]);
$detailImages = [];

foreach ($request->file('detail_images') as $image) {
    $detailImage = ModuleFileService::uploadImageAndMark(
        $image,
        $userId,
        false,
        'product_detail_image',
        $product->id
    );
    
    if ($detailImage) {
        $detailImages[] = $detailImage->id;
    }
}

// 批量标记(可选，如果 uploadImageAndMark 已经标记，这里不需要)
// ModuleFileService::batchMarkImagesAsUsed($detailImages);
```

### 3.4 获取图片URL

```php
use Modules\AFile\Services\ModuleFileService;

// 获取单个图片URL
$imageUrl = ModuleFileService::getImageUrl($imageId);

// 批量获取图片URL
$imageUrls = ModuleFileService::batchGetImageUrls([$imageId1, $imageId2, $imageId3]);
// 返回: [imageId1 => url1, imageId2 => url2, imageId3 => url3]

// 根据关联信息获取第一个图片URL
$coverUrl = ModuleFileService::getFirstImageUrlByRe('novel_chapter_cover', $chapterId);
```

### 3.5 业务删除时取消关联

```php
use Modules\AFile\Services\ModuleFileService;

// 方式1: 取消关联并删除文件(彻底清理)
public function deleteChapter($chapterId)
{
    $chapter = Chapter::findOrFail($chapterId);
    
    // 获取关联的封面图片
    $coverImage = ModuleFileService::getFirstImageByRe('novel_chapter_cover', $chapterId);
    
    // 删除章节
    $chapter->delete();
    
    // 取消关联并删除图片(彻底清理)
    if ($coverImage) {
        ModuleFileService::unlinkAndDeleteImage($coverImage->id);
    }
}

// 方式2: 取消关联但不删除文件(保留文件，标记悬空)
public function softDeleteChapter($chapterId)
{
    $chapter = Chapter::findOrFail($chapterId);
    
    // 获取关联的所有图片
    $images = ModuleFileService::getImagesByRe('novel_chapter_cover', $chapterId);
    
    // 删除章节
    $chapter->delete();
    
    // 批量取消关联(不删除文件，标记为悬空状态)
    $imageIds = $images->pluck('id')->toArray();
    ModuleFileService::batchUnlinkImages($imageIds, true);
}
```

### 3.6 查询关联文件

```php
use Modules\AFile\Services\ModuleFileService;

// 获取章节的所有封面图片
$coverImages = ModuleFileService::getImagesByRe('novel_chapter_cover', $chapterId);

// 获取第一个封面图片
$firstCover = ModuleFileService::getFirstImageByRe('novel_chapter_cover', $chapterId);

// 检查图片是否存在
if (ModuleFileService::imageExists($imageId)) {
    // 图片存在
}
```

### 3.7 临时文件使用

```php
use Modules\AFile\Services\ModuleFileService;

// 保存临时CSV文件
$tempPath = ModuleFileService::saveTempFile('csv', $csvContent);

if ($tempPath) {
    // 获取临时文件URL
    $tempUrl = ModuleFileService::getTempFileUrl($tempPath);
    
    // 返回给用户下载
    return response()->json(['url' => $tempUrl]);
}
```

---

## 4. 最佳实践

### 4.1 推荐使用一步完成方法

```php
// ✅ 推荐: 一步完成上传并标记
$image = ModuleFileService::uploadImageAndMark($file, $userId, false, 're_type', $reId);

// ❌ 不推荐: 分两步(容易遗漏标记)
$image = ModuleFileService::uploadImage($file, $userId, false, 're_type', $reId);
ModuleFileService::markImageAsUsed($image->id);  // 可能遗漏
```

### 4.2 业务删除时的处理策略

#### 策略1: 彻底清理(推荐)

适用于文件仅用于该业务，无其他用途的场景：

```php
// 删除业务时彻底清理关联文件
$business->delete();
$imageIds = $business->images->pluck('id')->toArray();
ModuleFileService::batchUnlinkAndDeleteImages($imageIds);
```

#### 策略2: 保留文件(标记悬空)

适用于文件可能被其他业务引用，或需要保留历史记录的场景：

```php
// 删除业务时取消关联，但保留文件
$business->delete();
$imageIds = $business->images->pluck('id')->toArray();
ModuleFileService::batchUnlinkImages($imageIds, true);
```

### 4.3 关联类型命名规范

```php
// ✅ 正确的命名规范
'novel_chapter_cover'      // 小说章节封面
'product_main_image'       // 商品主图
'user_avatar'              // 用户头像
'article_attachment'       // 文章附件

// ❌ 错误的命名
'cover'                    // 太简短，缺少模块和实体信息
'NovelChapterCover'        // 大驼峰，不符合规范
'novel-chapter-cover'      // 中划线，应使用下划线
```

### 4.4 批量操作提高性能

```php
// ✅ 推荐: 批量操作
$imageIds = $images->pluck('id')->toArray();
ModuleFileService::batchUnlinkAndDeleteImages($imageIds);

// ❌ 不推荐: 循环单个操作(性能差)
foreach ($images as $image) {
    ModuleFileService::unlinkAndDeleteImage($image->id);
}
```

### 4.5 异常处理

所有静态方法都有异常捕获，返回 null 或 false：

```php
// 上传失败返回 null
$image = ModuleFileService::uploadImage($file, $userId);
if (!$image) {
    // 上传失败，已自动记录日志
    return response()->json(['error' => '上传失败'], 500);
}

// 删除失败返回 false
$result = ModuleFileService::deleteFile($fileId);
if (!$result) {
    // 删除失败，已自动记录日志
    return response()->json(['error' => '删除失败'], 500);
}
```

---

## 5. 与 FileService 的区别

| 特性 | ModuleFileService | FileService |
|------|-------------------|-------------|
| 方法类型 | 全静态方法 | 实例方法 |
| 异常处理 | 自动捕获并记录日志 | 需要手动处理 |
| 使用方式 | 直接调用静态方法 | 需要实例化 |
| 适用场景 | 跨模块对接 | 模块内部使用 |
| 返回值 | null/false(失败时) | 抛出异常(失败时) |

**推荐使用场景**:
- **ModuleFileService**: 其他模块对接文件功能(简化调用)
- **FileService**: AFile 模块内部使用(更灵活)

---

## 6. 完整示例

### 创建业务并上传文件

```php
use Modules\AFile\Services\ModuleFileService;

class NovelController extends Controller
{
    /**
     * 创建小说章节
     */
    public function createChapter(Request $request, $novelId)
    {
        $userId = auth()->id();
        
        // 创建章节
        $chapter = Chapter::create([
            'novel_id' => $novelId,
            'title' => $request->title,
            'content' => $request->content,
        ]);
        
        // 上传封面图片(一步完成)
        if ($request->hasFile('cover')) {
            $coverImage = ModuleFileService::uploadImageAndMark(
                $request->file('cover'),
                $userId,
                false,
                'novel_chapter_cover',
                $chapter->id
            );
            
            if ($coverImage) {
                $chapter->cover_image_id = $coverImage->id;
                $chapter->save();
            }
        }
        
        // 批量上传内容图片
        if ($request->hasFile('content_images')) {
            foreach ($request->file('content_images') as $image) {
                ModuleFileService::uploadImageAndMark(
                    $image,
                    $userId,
                    false,
                    'novel_chapter_content',
                    $chapter->id
                );
            }
        }
        
        // 上传附件
        if ($request->hasFile('attachment')) {
            ModuleFileService::uploadFileAndMark(
                $request->file('attachment'),
                $userId,
                'novel_chapter_attachment',
                $chapter->id
            );
        }
        
        return response()->json([
            'chapter' => $chapter,
            'cover_url' => ModuleFileService::getImageUrl($chapter->cover_image_id),
        ]);
    }
    
    /**
     * 获取章节详情
     */
    public function showChapter($chapterId)
    {
        $chapter = Chapter::findOrFail($chapterId);
        
        // 获取封面图片URL
        $coverUrl = ModuleFileService::getImageUrl($chapter->cover_image_id);
        
        // 获取所有内容图片URL
        $contentImages = ModuleFileService::getImagesByRe('novel_chapter_content', $chapterId);
        $contentImageUrls = ModuleFileService::batchGetImageUrls(
            $contentImages->pluck('id')->toArray()
        );
        
        // 获取附件URL
        $attachment = ModuleFileService::getFirstFileByRe('novel_chapter_attachment', $chapterId);
        $attachmentUrl = $attachment ? ModuleFileService::getFileUrl($attachment->id) : '';
        
        return response()->json([
            'chapter' => $chapter,
            'cover_url' => $coverUrl,
            'content_image_urls' => $contentImageUrls,
            'attachment_url' => $attachmentUrl,
        ]);
    }
    
    /**
     * 删除章节(彻底清理)
     */
    public function deleteChapter($chapterId)
    {
        $chapter = Chapter::findOrFail($chapterId);
        
        // 获取所有关联的文件和图片
        $coverImage = ModuleFileService::getFirstImageByRe('novel_chapter_cover', $chapterId);
        $contentImages = ModuleFileService::getImagesByRe('novel_chapter_content', $chapterId);
        $attachments = ModuleFileService::getFilesByRe('novel_chapter_attachment', $chapterId);
        
        // 删除章节
        $chapter->delete();
        
        // 批量取消关联并删除(彻底清理)
        if ($coverImage) {
            ModuleFileService::unlinkAndDeleteImage($coverImage->id);
        }
        
        if ($contentImages->count() > 0) {
            ModuleFileService::batchUnlinkAndDeleteImages(
                $contentImages->pluck('id')->toArray()
            );
        }
        
        if ($attachments->count() > 0) {
            ModuleFileService::batchUnlinkAndDeleteFiles(
                $attachments->pluck('id')->toArray()
            );
        }
        
        return response()->json(['message' => '删除成功']);
    }
}
```

---

## 7. 总结

### 核心优势
1. **简单易用**: 全静态方法，无需实例化
2. **异常安全**: 自动捕获异常，避免程序崩溃
3. **日志完善**: 自动记录错误日志，便于排查
4. **性能优化**: 提供批量操作方法，避免 N+1 查询

### 使用建议
- ✅ 使用一步完成方法: `uploadImageAndMark()`
- ✅ 使用批量方法提高性能: `batchUnlinkAndDeleteImages()`
- ✅ 业务删除时彻底清理: `unlinkAndDeleteImage()`
- ✅ 遵守关联类型命名规范: `模块名_实体名_用途`

### 文件位置
[Modules/AFile/Services/ModuleFileService.php](../Services/ModuleFileService.php)