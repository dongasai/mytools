<?php

namespace Modules\AFile\Services;

use Illuminate\Http\UploadedFile;
use Modules\AFile\Models\FileFile;
use Modules\AFile\Models\FileImg;

/**
 * 模块文件服务类
 *
 * 专门用于跨模块对接的文件服务，提供全静态方法接口
 * 简化其他模块对文件功能的调用
 */
class ModuleFileService
{
    /**
     * 上传文件
     *
     * @param  UploadedFile  $file  上传的文件
     * @param  int  $userId  用户ID
     * @param  string  $reType  关联类型(格式: 模块名_实体名_用途)
     * @param  int  $reId  关联业务ID
     * @return FileFile 文件模型
     * @throws \Exception 上传失败时抛出异常（由调用方处理）
     */
    public static function uploadFile(UploadedFile $file, int $userId, string $reType = '', int $reId = 0): FileFile
    {
        return FileService::uploadFile($file, $userId, $reType, $reId);
    }

    /**
     * 上传图片
     *
     * @param  UploadedFile  $file  上传的图片
     * @param  int  $userId  用户ID
     * @param  bool  $private  是否私有(默认公开)
     * @param  string  $reType  关联类型(格式: 模块名_实体名_用途)
     * @param  int  $reId  关联业务ID
     * @return FileImg 图片模型
     * @throws \Exception 上传失败时抛出异常（由调用方处理）
     */
    public static function uploadImage(UploadedFile $file, int $userId, bool $private = false, string $reType = '', int $reId = 0): FileImg
    {
        return FileService::uploadImage($file, $userId, $private, $reType, $reId);
    }

    /**
     * 从已存在的文件路径创建图片记录
     *
     * 用于处理非 HTTP 上传的图片（如 AI 生成的图片）
     * 实现：从 local 磁盘临时目录 → 默认磁盘正式目录的文件迁移
     * 注意：磁盘配置由 AFile 模块内部管理，外部不允许传入 disk 参数
     *
     * @param string $path 文件存储路径（相对于 local 磁盘，通常是临时路径）
     * @param int $userId 用户ID
     * @param bool $private 是否私有(默认公开)
     * @param string $reType 关联类型(格式: 模块名_实体名_用途)
     * @param int $reId 关联业务ID
     * @return FileImg 图片模型
     * @throws \Exception 创建失败时抛出异常（由调用方处理）
     */
    public static function uploadImageForPath(string $path, int $userId, bool $private = false, string $reType = '', int $reId = 0): FileImg
    {
        return FileService::createImageFromPath($path, $userId, $reType, $reId, $private);
    }

    /**
     * 从已存在的文件路径创建文件记录
     *
     * 用于处理非 HTTP 上传的文件（如生成的文件）
     * 实现：从 local 磁盘临时目录 → 默认磁盘正式目录的文件迁移
     * 注意：磁盘配置由 AFile 模块内部管理，外部不允许传入 disk 参数
     *
     * @param string $path 文件存储路径（相对于 local 磁盘，通常是临时路径）
     * @param int $userId 用户ID
     * @param string $reType 关联类型(格式: 模块名_实体名_用途)
     * @param int $reId 关联业务ID
     * @param string $originalName 原始文件名（可选，默认使用路径中的文件名）
     * @return FileFile 文件模型
     * @throws \Exception 创建失败时抛出异常（由调用方处理）
     */
    public static function uploadFileForPath(string $path, int $userId, string $reType = '', int $reId = 0, string $originalName = ''): FileFile
    {
        return FileService::createFileFromPath($path, $userId, $reType, $reId, $originalName);
    }

    /**
     * 上传文件并标记为已使用(一步完成)
     *
     * @param  UploadedFile  $file  上传的文件
     * @param  int  $userId  用户ID
     * @param  string  $reType  关联类型(格式: 模块名_实体名_用途)
     * @param  int  $reId  关联业务ID
     * @return FileFile 文件模型
     * @throws \Exception 上传或标记失败时抛出异常（由调用方处理）
     */
    public static function uploadFileAndMark(UploadedFile $file, int $userId, string $reType, int $reId): FileFile
    {
        $fileModel = self::uploadFile($file, $userId, $reType, $reId);
        if ($fileModel->id) {
            self::markFileAsUsed($fileModel->id);
        }
        return $fileModel;
    }

    /**
     * 上传图片并标记为已使用(一步完成)
     *
     * @param  UploadedFile  $file  上传的图片
     * @param  int  $userId  用户ID
     * @param  bool  $private  是否私有(默认公开)
     * @param  string  $reType  关联类型(格式: 模块名_实体名_用途)
     * @param  int  $reId  关联业务ID
     * @return FileImg 图片模型
     * @throws \Exception 上传或标记失败时抛出异常（由调用方处理）
     */
    public static function uploadImageAndMark(UploadedFile $file, int $userId, bool $private = false, string $reType, int $reId): FileImg
    {
        $imageModel = self::uploadImage($file, $userId, $private, $reType, $reId);
        if ($imageModel->id) {
            self::markImageAsUsed($imageModel->id);
        }
        return $imageModel;
    }

    /**
     * 标记文件为已使用
     *
     * @param  int  $fileId  文件ID
     * @return bool 是否成功
     * @throws \Exception 操作失败时抛出异常（由调用方处理）
     */
    public static function markFileAsUsed(int $fileId): bool
    {
        return FileService::markFileAsUsed($fileId);
    }

    /**
     * 标记图片为已使用
     *
     * @param  int  $imageId  图片ID
     * @return bool 是否成功
     * @throws \Exception 操作失败时抛出异常（由调用方处理）
     */
    public static function markImageAsUsed(int $imageId): bool
    {
        return FileService::markImageAsUsed($imageId);
    }

    /**
     * 批量标记文件为已使用
     *
     * @param  array  $fileIds  文件ID数组
     * @return bool 是否成功
     * @throws \Exception 操作失败时抛出异常（由调用方处理）
     */
    public static function batchMarkFilesAsUsed(array $fileIds): bool
    {
        if (empty($fileIds)) {
            return false;
        }
        return FileService::batchMarkAsUsed($fileIds);
    }

    /**
     * 批量标记图片为已使用
     *
     * @param  array  $imageIds  图片ID数组
     * @return bool 是否成功
     * @throws \Exception 操作失败时抛出异常（由调用方处理）
     */
    public static function batchMarkImagesAsUsed(array $imageIds): bool
    {
        if (empty($imageIds)) {
            return false;
        }

        foreach ($imageIds as $imageId) {
            FileService::markImageAsUsed($imageId);
        }
        return true;
    }

    /**
     * 取消文件关联(解除业务关联，不删除文件)
     *
     * @param  int  $fileId  文件ID
     * @param  bool  $markDangling  是否标记为悬空状态(默认true)
     * @return bool 是否成功
     * @throws \Exception 操作失败时抛出异常（由调用方处理）
     */
    public static function unlinkFile(int $fileId, bool $markDangling = true): bool
    {
        return FileService::unlinkFile($fileId, $markDangling);
    }

    /**
     * 取消图片关联(解除业务关联，不删除图片)
     *
     * @param  int  $imageId  图片ID
     * @param  bool  $markDangling  是否标记为悬空状态(默认true)
     * @return bool 是否成功
     * @throws \Exception 操作失败时抛出异常（由调用方处理）
     */
    public static function unlinkImage(int $imageId, bool $markDangling = true): bool
    {
        return FileService::unlinkImage($imageId, $markDangling);
    }

    /**
     * 取消文件关联并删除文件(彻底清理)
     *
     * @param  int  $fileId  文件ID
     * @return bool 是否成功
     * @throws \Exception 操作失败时抛出异常（由调用方处理）
     */
    public static function unlinkAndDeleteFile(int $fileId): bool
    {
        return FileService::unlinkAndDeleteFile($fileId);
    }

    /**
     * 取消图片关联并删除图片(彻底清理)
     *
     * @param  int  $imageId  图片ID
     * @return bool 是否成功
     * @throws \Exception 操作失败时抛出异常（由调用方处理）
     */
    public static function unlinkAndDeleteImage(int $imageId): bool
    {
        return FileService::unlinkAndDeleteImage($imageId);
    }

    /**
     * 批量取消文件关联
     *
     * @param  array  $fileIds  文件ID数组
     * @param  bool  $markDangling  是否标记为悬空状态(默认true)
     * @return bool 是否成功
     * @throws \Exception 操作失败时抛出异常（由调用方处理）
     */
    public static function batchUnlinkFiles(array $fileIds, bool $markDangling = true): bool
    {
        if (empty($fileIds)) {
            return false;
        }
        return FileService::batchUnlinkFiles($fileIds, $markDangling);
    }

    /**
     * 批量取消图片关联
     *
     * @param  array  $imageIds  图片ID数组
     * @param  bool  $markDangling  是否标记为悬空状态(默认true)
     * @return bool 是否成功
     * @throws \Exception 操作失败时抛出异常（由调用方处理）
     */
    public static function batchUnlinkImages(array $imageIds, bool $markDangling = true): bool
    {
        if (empty($imageIds)) {
            return false;
        }
        return FileService::batchUnlinkImages($imageIds, $markDangling);
    }

    /**
     * 批量取消文件关联并删除文件
     *
     * @param  array  $fileIds  文件ID数组
     * @return bool 是否成功
     * @throws \Exception 操作失败时抛出异常（由调用方处理）
     */
    public static function batchUnlinkAndDeleteFiles(array $fileIds): bool
    {
        if (empty($fileIds)) {
            return false;
        }
        return FileService::batchUnlinkAndDeleteFiles($fileIds);
    }

    /**
     * 批量取消图片关联并删除图片
     *
     * @param  array  $imageIds  图片ID数组
     * @return bool 是否成功
     * @throws \Exception 操作失败时抛出异常（由调用方处理）
     */
    public static function batchUnlinkAndDeleteImages(array $imageIds): bool
    {
        if (empty($imageIds)) {
            return false;
        }
        return FileService::batchUnlinkAndDeleteImages($imageIds);
    }

    /**
     * 删除文件
     *
     * @param  int  $fileId  文件ID
     * @return bool 是否成功
     * @throws \Exception 操作失败时抛出异常（由调用方处理）
     */
    public static function deleteFile(int $fileId): bool
    {
        return FileService::deleteFile($fileId);
    }

    /**
     * 删除图片
     *
     * @param  int  $imageId  图片ID
     * @return bool 是否成功
     * @throws \Exception 操作失败时抛出异常（由调用方处理）
     */
    public static function deleteImage(int $imageId): bool
    {
        return FileService::deleteImage($imageId);
    }

    /**
     * 批量删除文件
     *
     * @param  array  $fileIds  文件ID数组
     * @return bool 是否成功
     * @throws \Exception 操作失败时抛出异常（由调用方处理）
     */
    public static function batchDeleteFiles(array $fileIds): bool
    {
        if (empty($fileIds)) {
            return false;
        }

        foreach ($fileIds as $fileId) {
            FileService::deleteFile($fileId);
        }
        return true;
    }

    /**
     * 批量删除图片
     *
     * @param  array  $imageIds  图片ID数组
     * @return bool 是否成功
     * @throws \Exception 操作失败时抛出异常（由调用方处理）
     */
    public static function batchDeleteImages(array $imageIds): bool
    {
        if (empty($imageIds)) {
            return false;
        }

        foreach ($imageIds as $imageId) {
            FileService::deleteImage($imageId);
        }
        return true;
    }

    /**
     * 获取文件URL
     *
     * @param  int  $fileId  文件ID
     * @return string 文件URL
     * @throws \Exception 操作失败时抛出异常（由调用方处理）
     */
    public static function getFileUrl(int $fileId): string
    {
        return FileService::getFileUrl($fileId);
    }

    /**
     * 获取图片URL
     *
     * @param  int  $imageId  图片ID
     * @param  bool  $private  是否私有(默认公开)
     * @return string 图片URL
     * @throws \Exception 操作失败时抛出异常（由调用方处理）
     */
    public static function getImageUrl(int $imageId, bool $private = false): string
    {
        return ImgService::getPicUrl4Id($imageId, $private);
    }

    /**
     * 批量获取图片URL
     *
     * @param  array  $imageIds  图片ID数组
     * @param  bool  $private  是否私有(默认公开)
     * @return array 图片URL数组 [imageId => url]
     * @throws \Exception 操作失败时抛出异常（由调用方处理）
     */
    public static function batchGetImageUrls(array $imageIds, bool $private = false): array
    {
        if (empty($imageIds)) {
            return [];
        }

        $urls = [];
        foreach ($imageIds as $imageId) {
            $urls[$imageId] = ImgService::getPicUrl4Id($imageId, $private);
        }

        return $urls;
    }

    /**
     * 获取文件模型
     *
     * @param  int  $fileId  文件ID
     * @return FileFile|null 文件模型
     */
    public static function getFile(int $fileId): ?FileFile
    {
        return FileFile::find($fileId);
    }

    /**
     * 获取图片模型
     *
     * @param  int  $imageId  图片ID
     * @return FileImg|null 图片模型
     */
    public static function getImage(int $imageId): ?FileImg
    {
        return FileImg::find($imageId);
    }

    /**
     * 根据关联信息获取文件列表
     *
     * @param  string  $reType  关联类型
     * @param  int  $reId  关联业务ID
     * @return \Illuminate\Database\Eloquent\Collection 文件集合
     */
    public static function getFilesByRe(string $reType, int $reId)
    {
        return FileFile::where('re_type', $reType)
            ->where('re_id', $reId)
            ->get();
    }

    /**
     * 根据关联信息获取图片列表
     *
     * @param  string  $reType  关联类型
     * @param  int  $reId  关联业务ID
     * @return \Illuminate\Database\Eloquent\Collection 图片集合
     */
    public static function getImagesByRe(string $reType, int $reId)
    {
        return FileImg::where('re_type', $reType)
            ->where('re_id', $reId)
            ->get();
    }

    /**
     * 根据关联信息获取第一个文件
     *
     * @param  string  $reType  关联类型
     * @param  int  $reId  关联业务ID
     * @return FileFile|null 文件模型
     */
    public static function getFirstFileByRe(string $reType, int $reId): ?FileFile
    {
        return FileFile::where('re_type', $reType)
            ->where('re_id', $reId)
            ->first();
    }

    /**
     * 根据关联信息获取第一个图片
     *
     * @param  string  $reType  关联类型
     * @param  int  $reId  关联业务ID
     * @return FileImg|null 图片模型
     */
    public static function getFirstImageByRe(string $reType, int $reId): ?FileImg
    {
        return FileImg::where('re_type', $reType)
            ->where('re_id', $reId)
            ->first();
    }

    /**
     * 根据关联信息获取第一个图片URL
     *
     * @param  string  $reType  关联类型
     * @param  int  $reId  关联业务ID
     * @param  bool  $private  是否私有(默认公开)
     * @return string 图片URL
     * @throws \Exception 获取URL失败时抛出异常（由调用方处理）
     */
    public static function getFirstImageUrlByRe(string $reType, int $reId, bool $private = false): string
    {
        $image = self::getFirstImageByRe($reType, $reId);
        if (!$image) {
            return '';
        }

        return self::getImageUrl($image->id, $private);
    }

    /**
     * 检查文件是否存在
     *
     * @param  int  $fileId  文件ID
     * @return bool 是否存在
     */
    public static function fileExists(int $fileId): bool
    {
        return FileFile::where('id', $fileId)->exists();
    }

    /**
     * 检查图片是否存在
     *
     * @param  int  $imageId  图片ID
     * @return bool 是否存在
     */
    public static function imageExists(int $imageId): bool
    {
        return FileImg::where('id', $imageId)->exists();
    }

    /**
     * 保存临时文件
     *
     * @param  string  $ext  文件扩展名
     * @param  string  $content  文件内容
     * @return string 临时文件路径
     * @throws \Exception 操作失败时抛出异常（由调用方处理）
     */
    public static function saveTempFile(string $ext, string $content): string
    {
        return FileService::saveTempFile($ext, $content);
    }

    /**
     * 获取临时文件URL
     *
     * @param  string  $path  临时文件路径
     * @return string 临时文件URL
     * @throws \Exception 操作失败时抛出异常（由调用方处理）
     */
    public static function getTempFileUrl(string $path): string
    {
        return FileService::getTempFileUrl($path);
    }

    /**
     * 保存临时文件到公开存储
     *
     * 用于需要公开访问的临时文件（如导出文件）
     * 文件保存到 public 磁盘，返回完整的公开访问 URL
     *
     * @param string $ext 文件扩展名
     * @param string $content 文件内容
     * @return string 公开访问URL
     * @throws \Exception 保存失败时抛出异常（由调用方处理）
     */
    public static function saveTempFilePublic(string $ext, string $content): string
    {
        return FileService::saveTempFilePublic($ext, $content);
    }

    /**
     * 保存上传的临时文件
     *
     * 接收 UploadedFile 对象，保存到临时储存，返回相对路径（如 temp/202608/14/xxx.xlsx）
     * 不写数据库，仅返回相对路径供后续处理
     *
     * @param \Illuminate\Http\UploadedFile $file 上传的文件对象
     * @return string 相对路径
     * @throws \Exception 保存失败时抛出异常（由调用方处理）
     */
    public static function saveTempUploadFile(\Illuminate\Http\UploadedFile $file): string
    {
        $temporaryService = new TemporaryService;

        $ext = $file->getClientOriginalExtension();
        if (empty($ext)) {
            $ext = $file->guessExtension() ?: 'tmp';
        }

        $content = $file->getContent();

        return $temporaryService->save($ext, $content);
    }
}
