<?php

namespace Modules\AFile\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Modules\AFile\Events\MarkFileUsedEvent;
use Modules\AFile\Events\MarkImageUsedEvent;
use Modules\AFile\Logics\FileLogic;
use Modules\AFile\Models\FileFile;
use Modules\AFile\Models\FileImg;

/**
 * 文件服务类
 *
 * 提供文件上传、下载、删除等服务
 * 所有方法均为静态方法，符合Service层规范
 */
class FileService
{
    /**
     * 上传文件
     *
     * @param  UploadedFile  $file  上传的文件
     * @param  int  $userId  用户ID
     * @param  string  $reType  关联类型
     * @param  int  $reId  关联ID
     * @return FileFile 文件模型
     */
    public static function uploadFile(UploadedFile $file, int $userId, string $reType = '', int $reId = 0): FileFile
    {
        return FileLogic::uploadFile($file, $userId, $reType, $reId);
    }

    /**
     * 上传图片
     *
     * @param  UploadedFile  $file  上传的图片
     * @param  int  $userId  用户ID
     * @param  bool  $private  是否私有
     * @param  string  $reType  关联类型
     * @param  int  $reId  关联ID
     * @return FileImg 图片模型
     */
    public static function uploadImage(UploadedFile $file, int $userId, bool $private = false, string $reType = '', int $reId = 0): FileImg
    {
        return FileLogic::uploadImage($file, $userId, $private, $reType, $reId);
    }

    /**
     * 获取文件URL
     *
     * @param  int  $fileId  文件ID
     * @return string 文件URL
     */
    public static function getFileUrl(int $fileId): string
    {
        return FileLogic::getFileUrl($fileId);
    }

    /**
     * 获取图片URL
     *
     * @param  int  $imageId  图片ID
     * @param  bool  $private  是否私有
     * @return string 图片URL
     */
    public static function getImageUrl(int $imageId, bool $private = false): string
    {
        return FileLogic::getImageUrl($imageId, $private);
    }

    /**
     * 删除文件
     *
     * @param  int  $fileId  文件ID
     * @return bool 是否成功
     */
    public static function deleteFile(int $fileId): bool
    {
        return FileLogic::deleteFile($fileId);
    }

    /**
     * 删除图片
     *
     * @param  int  $imageId  图片ID
     * @return bool 是否成功
     */
    public static function deleteImage(int $imageId): bool
    {
        return FileLogic::deleteImage($imageId);
    }

    /**
     * 下载文件
     *
     * @param  int  $fileId  文件ID
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|null 下载响应或 null（文件不存在）
     */
    public static function downloadFile(int $fileId): ?\Symfony\Component\HttpFoundation\StreamedResponse
    {
        return FileLogic::downloadFile($fileId);
    }

    /**
     * 保存临时文件
     *
     * @param  string  $ext  文件扩展名
     * @param  string  $content  文件内容
     * @return string 临时文件路径
     */
    public static function saveTempFile(string $ext, string $content): string
    {
        return FileLogic::saveTempFile($ext, $content);
    }

    /**
     * 获取临时文件URL
     *
     * @param  string  $path  临时文件路径
     * @return string 临时文件URL
     */
    public static function getTempFileUrl(string $path): string
    {
        return FileLogic::getTempFileUrl($path);
    }

    /**
     * 保存临时文件到公开存储
     *
     * 用于需要公开访问的临时文件（如导出文件）
     *
     * @param string $ext 文件扩展名
     * @param string $content 文件内容
     * @return string 公开访问URL
     */
    public static function saveTempFilePublic(string $ext, string $content): string
    {
        return FileLogic::saveTempFilePublic($ext, $content);
    }

    /**
     * 标记文件为已使用
     *
     * @param  int  $fileId  文件ID
     * @return bool 是否成功
     */
    public static function markFileAsUsed(int $fileId): bool
    {
        // 仅当 status='normal' 且 used_at=null 时才更新
        $updated = DB::table('file_files')
            ->where('id', $fileId)
            ->where('status', 'normal')
            ->whereNull('used_at')
            ->update([
                'status' => 'linked',
                'used_at' => now(),
                'updated_at' => now(),
            ]);

        if ($updated > 0) {
            // 触发文件使用事件（在事务外）
            MarkFileUsedEvent::dispatch($fileId);
            return true;
        }

        return false;
    }

    /**
     * 标记图片为已使用
     *
     * @param  int  $imageId  图片ID
     * @return bool 是否成功
     */
    public static function markImageAsUsed(int $imageId): bool
    {
        // 仅当 status='normal' 且 used_at=null 时才更新
        $updated = DB::table('file_imgs')
            ->where('id', $imageId)
            ->where('status', 'normal')
            ->whereNull('used_at')
            ->update([
                'status' => 'linked',
                'used_at' => now(),
                'updated_at' => now(),
            ]);

        if ($updated > 0) {
            // 触发图片使用事件（在事务外）
            MarkImageUsedEvent::dispatch($imageId);
            return true;
        }

        return false;
    }

    /**
     * 批量标记文件为已使用
     *
     * @param  array  $fileIds  文件ID数组
     * @return bool 是否成功
     */
    public static function batchMarkAsUsed(array $fileIds): bool
    {
        if (empty($fileIds)) {
            return false;
        }

        // 批量更新（避免N+1查询）
        $updated = DB::table('file_files')
            ->whereIn('id', $fileIds)
            ->where('status', 'normal')
            ->whereNull('used_at')
            ->update([
                'status' => 'linked',
                'used_at' => now(),
                'updated_at' => now(),
            ]);

        // 如果有更新，批量触发事件（在事务外）
        if ($updated > 0) {
            foreach ($fileIds as $fileId) {
                MarkFileUsedEvent::dispatch($fileId);
            }
            return true;
        }

        return false;
    }

    /**
     * 取消文件关联(解除业务关联，不删除文件)
     *
     * @param  int  $fileId  文件ID
     * @param  bool  $markDangling  是否标记为悬空状态
     * @return bool 是否成功
     */
    public static function unlinkFile(int $fileId, bool $markDangling = true): bool
    {
        $file = FileFile::find($fileId);
        if (!$file) {
            return false;
        }

        // 清空关联信息
        $file->re_type = '';
        $file->re_id = 0;

        // 根据参数决定状态
        if ($markDangling) {
            $file->status = 'dangling';
            $file->dangling_at = now();
        }

        $file->updated_at = now();
        $result = $file->save();

        return $result;
    }

    /**
     * 取消图片关联(解除业务关联，不删除图片)
     *
     * @param  int  $imageId  图片ID
     * @param  bool  $markDangling  是否标记为悬空状态
     * @return bool 是否成功
     */
    public static function unlinkImage(int $imageId, bool $markDangling = true): bool
    {
        $image = FileImg::find($imageId);
        if (!$image) {
            return false;
        }

        // 清空关联信息
        $image->re_type = '';
        $image->re_id = 0;

        // 根据参数决定状态
        if ($markDangling) {
            $image->status = 'dangling';
            $image->dangling_at = now();
        }

        $image->updated_at = now();
        $result = $image->save();

        return $result;
    }

    /**
     * 取消文件关联并删除文件(解除关联后删除文件)
     *
     * @param  int  $fileId  文件ID
     * @return bool 是否成功
     */
    public static function unlinkAndDeleteFile(int $fileId): bool
    {
        // 先取消关联
        self::unlinkFile($fileId, false);

        // 再删除文件
        return self::deleteFile($fileId);
    }

    /**
     * 取消图片关联并删除图片(解除关联后删除图片)
     *
     * @param  int  $imageId  图片ID
     * @return bool 是否成功
     */
    public static function unlinkAndDeleteImage(int $imageId): bool
    {
        // 先取消关联
        self::unlinkImage($imageId, false);

        // 再删除图片
        return self::deleteImage($imageId);
    }

    /**
     * 批量取消文件关联(解除业务关联，不删除文件)
     *
     * @param  array  $fileIds  文件ID数组
     * @param  bool  $markDangling  是否标记为悬空状态
     * @return bool 是否成功
     */
    public static function batchUnlinkFiles(array $fileIds, bool $markDangling = true): bool
    {
        if (empty($fileIds)) {
            return false;
        }

        // 批量更新（避免N+1查询）
        $updateData = [
            're_type' => '',
            're_id' => 0,
            'updated_at' => now(),
        ];

        if ($markDangling) {
            $updateData['status'] = 'dangling';
            $updateData['dangling_at'] = now();
        }

        $updated = DB::table('file_files')
            ->whereIn('id', $fileIds)
            ->update($updateData);

        return $updated > 0;
    }

    /**
     * 批量取消图片关联(解除业务关联，不删除图片)
     *
     * @param  array  $imageIds  图片ID数组
     * @param  bool  $markDangling  是否标记为悬空状态
     * @return bool 是否成功
     */
    public static function batchUnlinkImages(array $imageIds, bool $markDangling = true): bool
    {
        if (empty($imageIds)) {
            return false;
        }

        // 扇量更新（避免N+1查询）
        $updateData = [
            're_type' => '',
            're_id' => 0,
            'updated_at' => now(),
        ];

        if ($markDangling) {
            $updateData['status'] = 'dangling';
            $updateData['dangling_at'] = now();
        }

        $updated = DB::table('file_imgs')
            ->whereIn('id', $imageIds)
            ->update($updateData);

        return $updated > 0;
    }

    /**
     * 批量取消文件关联并删除文件（事务保护）
     *
     * @param  array  $fileIds  文件ID数组
     * @return bool 是否成功
     * @throws \Exception 操作失败时抛出异常并回滚
     */
    public static function batchUnlinkAndDeleteFiles(array $fileIds): bool
    {
        if (empty($fileIds)) {
            return false;
        }

        return DB::transaction(function () use ($fileIds) {
            foreach ($fileIds as $fileId) {
                self::unlinkAndDeleteFile($fileId);
            }
            return true;
        });
    }

    /**
     * 批量取消图片关联并删除图片（事务保护）
     *
     * @param  array  $imageIds  图片ID数组
     * @return bool 是否成功
     * @throws \Exception 操作失败时抛出异常并回滚
     */
    public static function batchUnlinkAndDeleteImages(array $imageIds): bool
    {
        if (empty($imageIds)) {
            return false;
        }

        return DB::transaction(function () use ($imageIds) {
            foreach ($imageIds as $imageId) {
                self::unlinkAndDeleteImage($imageId);
            }
            return true;
        });
    }

    /**
     * 从已存在的文件路径创建图片记录
     *
     * 用于处理非 HTTP 上传的图片（如 AI 生成的图片）
     *
     * @param  string  $path  文件存储路径（相对于默认磁盘）
     * @param  int  $userId  用户ID
     * @param  string  $reType  关联类型
     * @param  int  $reId  关联ID
     * @param  bool  $private  是否私有
     * @return FileImg 图片模型
     * @throws \Exception 文件不存在或创建失败时抛出异常
     */
    public static function createImageFromPath(string $path, int $userId, string $reType = '', int $reId = 0, bool $private = false): FileImg
    {
        return FileLogic::createImageFromPath($path, $userId, $reType, $reId, $private);
    }

    /**
     * 从已存在的文件路径创建文件记录
     *
     * 用于处理非 HTTP 上传的文件（如生成的文件）
     *
     * @param  string  $path  文件存储路径（相对于默认磁盘）
     * @param  int  $userId  用户ID
     * @param  string  $reType  关联类型
     * @param  int  $reId  关联ID
     * @param  string  $originalName  原始文件名（可选）
     * @return FileFile 文件模型
     * @throws \Exception 文件不存在或创建失败时抛出异常
     */
    public static function createFileFromPath(string $path, int $userId, string $reType = '', int $reId = 0, string $originalName = ''): FileFile
    {
        return FileLogic::createFileFromPath($path, $userId, $reType, $reId, $originalName);
    }

    /**
     * 获取用户文件列表（带筛选条件）
     *
     * @param  int  $userId  用户ID
     * @param  array  $filters  筛选条件
     * @return array 分页结果数组
     */
    public static function getUserFileList(int $userId, array $filters = []): array
    {
        $query = FileFile::query()->orderBy('created_at', 'desc');

        // 仅返回用户自己的文件
        $query->where('user_id', $userId);

        // 应用筛选条件
        if (isset($filters['re_type'])) {
            $query->where('re_type', $filters['re_type']);
        }
        if (isset($filters['re_id'])) {
            $query->where('re_id', $filters['re_id']);
        }
        if (isset($filters['type1'])) {
            $query->where('type1', $filters['type1']);
        }

        // 分页
        $perPage = $filters['per_page'] ?? 20;
        $page = $filters['page'] ?? 1;
        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        // 格式化返回数据
        $items = collect($paginator->items())->map(function ($file) {
            return [
                'id' => $file->id,
                'original_name' => $file->o_name,
                'file_size' => $file->fsize,
                'file_type' => $file->type1,
                're_type' => $file->re_type,
                're_id' => $file->re_id,
                'url' => self::getFileUrl($file->id),
                'created_at' => $file->created_at->toIso8601String(),
            ];
        });

        return [
            'items' => $items,
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
        ];
    }
}
