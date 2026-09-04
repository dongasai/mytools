<?php

namespace Modules\AFile\Logics;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage as FacadesStorage;
use Modules\AFile\Logics\StorageConfig;
use Modules\AFile\Events\FileDeletedEvent;
use Modules\AFile\Events\FileUploadedEvent;
use Modules\AFile\Events\ImageDeletedEvent;
use Modules\AFile\Events\ImageUploadedEvent;
use Modules\AFile\Models\FileFile;
use Modules\AFile\Models\FileImg;
use Modules\AFile\Services\ImgService;
use Modules\AFile\Services\TemporaryService;
use Modules\AFile\Services\StorageConfigService;
use Modules\AFile\Services\UploadService;
use Modules\Application\Services\SystemLogService;

/**
 * 文件逻辑类
 *
 * 实现文件操作的具体逻辑
 * 所有方法均为静态方法，无状态设计
 */
class FileLogic
{
    /**
     * 上传文件
     *
     * @param  UploadedFile  $file  上传的文件
     * @param  int  $userId  用户ID
     * @param  string  $reType  关联类型
     * @param  int  $reId  关联ID
     * @return FileFile 文件模型
     * @throws \Exception 存储配置不存在或文件上传失败时抛出异常
     */
    public static function uploadFile(UploadedFile $file, int $userId, string $reType = '', int $reId = 0): FileFile
    {
        try {
            $dir = DirLogic::getDir($userId);
            $path = $dir.'/'.uniqid().'.'.$file->getClientOriginalExtension();

            // 从数据库获取默认存储配置
            $defaultDiskConfig = StorageConfigService::getDefaultDisk();

            if (!$defaultDiskConfig) {
                throw new \Exception('未找到默认存储配置');
            }

            $diskName = $defaultDiskConfig->name;

            // 动态注册磁盘配置（确保配置已加载）
            $config = array_merge(
                ['driver' => $defaultDiskConfig->driver],
                $defaultDiskConfig->config,
                [
                    'throw' => true, // 强制抛出异常以便调试
                    'timeout' => 60,
                    'connect_timeout' => 10,
                ]
            );
            config(["filesystems.disks.{$diskName}" => $config]);

            $res = FacadesStorage::disk($diskName)->putFileAs(dirname($path), $file, basename($path));

            if ($res === false) {
                throw new \Exception('服务器错误,请联系客服.');
            }

            $fileModel = new FileFile;
            $fileModel->storage_disk = $diskName;
            $fileModel->path = $path;
            $fileModel->re_type = $reType;
            $fileModel->re_id = $reId;
            $fileModel->o_name = $file->getClientOriginalName();
            $fileModel->fsize = $file->getSize();
            $fileModel->type1 = $file->getClientOriginalExtension();
            $fileModel->save();

            // 触发文件上传事件
            FileUploadedEvent::dispatch($fileModel);

            return $fileModel;
        } catch (\Throwable $e) {
            SystemLogService::exception('afile', $e, [
                'operation' => 'uploadFile',
                'filename' => $file->getClientOriginalName(),
                'user_id' => $userId,
            ]);
            throw $e;
        }
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
        $fileImg = UploadService::uploadImg($file, $userId, $private);

        if (! empty($reType) && $reId > 0) {
            $fileImg->re_type = $reType;
            $fileImg->re_id = $reId;
            $fileImg->save();
        }

        // 触发图片上传事件
        ImageUploadedEvent::dispatch($fileImg);

        return $fileImg;
    }

    /**
     * 获取文件URL
     *
     * @param  int  $fileId  文件ID
     * @return string 文件URL（通过API下载路由访问）
     */
    public static function getFileUrl(int $fileId): string
    {
        $file = FileFile::find($fileId);
        if (! $file) {
            return '';
        }

        // 文件通过API下载路由访问
        return rtrim(config('app.url'), '/') . '/api/v1/files/' . $file->id . '/download';
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
        return ImgService::getPicUrl4Id($imageId, $private);
    }

    /**
     * 删除文件
     *
     * @param  int  $fileId  文件ID
     * @return bool 是否成功
     */
    public static function deleteFile(int $fileId): bool
    {
        $file = FileFile::find($fileId);
        if (! $file) {
            return false;
        }

        $disk = $file->storage_disk ?: StorageConfig::getStorage();
        $path = $file->path;
        $result = $file->delete();

        if ($result) {
            // 触发文件删除事件
            FileDeletedEvent::dispatch($fileId, $path, $disk);
        }

        return $result;
    }

    /**
     * 删除图片
     *
     * @param  int  $imageId  图片ID
     * @return bool 是否成功
     */
    public static function deleteImage(int $imageId): bool
    {
        $image = FileImg::find($imageId);
        if (! $image) {
            return false;
        }

        $path = $image->path;
        $disk = $image->storage_disk;
        $result = $image->delete();

        if ($result) {
            // 触发图片删除事件
            ImageDeletedEvent::dispatch($imageId, $path, $disk);
        }

        return $result;
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
        $temporaryService = new TemporaryService();
        return $temporaryService->save($ext, $content);
    }

    /**
     * 获取临时文件URL
     *
     * @param  string  $path  临时文件路径
     * @return string 临时文件URL
     */
    public static function getTempFileUrl(string $path): string
    {
        $temporaryService = new TemporaryService();
        return $temporaryService->getDownUrl($path);
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
        $temporaryService = new TemporaryService();
        return $temporaryService->savePublic($ext, $content);
    }

    /**
     * 从已存在的文件路径创建图片记录
     *
     * 用于处理非 HTTP 上传的图片（如 AI 生成的图片）
     * 实现：从 local 磁盘临时目录 → 默认磁盘正式目录的文件迁移
     *
     * @param string $path 文件存储路径（相对于 local 磁盘，通常是临时路径）
     * @param int $userId 用户ID
     * @param string $reType 关联类型
     * @param int $reId 关联ID
     * @param bool $private 是否私有
     * @return FileImg 图片模型
     * @throws \Exception 文件不存在或创建失败时抛出异常
     */
    public static function createImageFromPath(string $path, int $userId, string $reType = '', int $reId = 0, bool $private = false): FileImg
    {
        try {
            // 从数据库获取默认存储配置
            $defaultDiskConfig = StorageConfigService::getDefaultDisk();

            if (!$defaultDiskConfig) {
                throw new \Exception('未找到默认存储配置');
            }

            $targetDisk = $defaultDiskConfig->name;

            // 步骤1: 检查源文件是否存在于 local 磁盘
            if (!FacadesStorage::disk('local')->exists($path)) {
                throw new \Exception('文件不存在: ' . $path);
            }

            // 步骤2: 从 local 磁盘读取文件内容
            $imageContent = FacadesStorage::disk('local')->get($path);
            $fsize = strlen($imageContent);

            // 步骤3: 获取图片尺寸信息
            $width = 0;
            $height = 0;
            $type1 = 'png'; // 默认 PNG

            try {
                $imageInfo = getimagesizefromstring($imageContent);
                if ($imageInfo !== false) {
                    $width = $imageInfo[0];
                    $height = $imageInfo[1];
                    $type1 = image_type_to_extension($imageInfo[2], false);
                }
            } catch (\Throwable $e) {
                // 如果读取失败，使用默认值
            }

            // 步骤4: 生成目标磁盘的新路径（正式存储路径）
            $targetPath = DirLogic::getDir($userId) . '/' . uniqid() . '.' . $type1;

            // 步骤5: 将文件保存到目标磁盘
            $saveResult = FacadesStorage::disk($targetDisk)->put($targetPath, $imageContent);
            if ($saveResult === false) {
                throw new \Exception('文件保存到目标磁盘失败');
            }

            // 步骤6: 删除 local 临时文件
            FacadesStorage::disk('local')->delete($path);

            // 步骤7: 创建图片记录（记录目标磁盘路径）
            $fileImg = new FileImg;
            $fileImg->storage_disk = $targetDisk;
            $fileImg->path = $targetPath; // 使用新路径，不是临时路径
            $fileImg->user_id = $userId;
            $fileImg->re_type = $reType;
            $fileImg->re_id = $reId;
            $fileImg->o_name = basename($path); // 使用原临时文件名作为原名
            $fileImg->fsize = $fsize;
            $fileImg->width = $width;
            $fileImg->height = $height;
            $fileImg->type1 = $type1;
            $fileImg->private = (int) $private;
            $fileImg->status = 'normal'; // 正常状态
            $fileImg->save();

            // 触发图片上传事件
            ImageUploadedEvent::dispatch($fileImg);

            return $fileImg;
        } catch (\Throwable $e) {
            SystemLogService::exception('afile', $e, [
                'operation' => 'createImageFromPath',
                'path' => $path,
                'user_id' => $userId,
            ]);
            throw $e;
        }
    }

    /**
     * 从已存在的文件路径创建文件记录
     *
     * 用于处理非 HTTP 上传的文件（如生成的文件）
     * 实现：从 local 磁盘临时目录 → 默认磁盘正式目录的文件迁移
     *
     * @param string $path 文件存储路径（相对于 local 磁盘，通常是临时路径）
     * @param int $userId 用户ID
     * @param string $reType 关联类型
     * @param int $reId 关联ID
     * @param string $originalName 原始文件名（可选，默认使用路径中的文件名）
     * @return FileFile 文件模型
     * @throws \Exception 文件不存在或创建失败时抛出异常
     */
    public static function createFileFromPath(string $path, int $userId, string $reType = '', int $reId = 0, string $originalName = ''): FileFile
    {
        try {
            // 从数据库获取默认存储配置
            $defaultDiskConfig = StorageConfigService::getDefaultDisk();

            if (!$defaultDiskConfig) {
                throw new \Exception('未找到默认存储配置');
            }

            $targetDisk = $defaultDiskConfig->name;

            // 步骤1: 检查源文件是否存在于 local 磁盘
            if (!FacadesStorage::disk('local')->exists($path)) {
                throw new \Exception('文件不存在: ' . $path);
            }

            // 步骤2: 从 local 磁盘读取文件内容
            $fileContent = FacadesStorage::disk('local')->get($path);
            $fsize = strlen($fileContent);

            // 步骤3: 获取文件扩展名
            $extension = pathinfo($path, PATHINFO_EXTENSION);

            // 步骤4: 生成目标磁盘的新路径（正式存储路径）
            $targetPath = DirLogic::getDir($userId) . '/' . uniqid() . '.' . $extension;

            // 步骤5: 将文件保存到目标磁盘
            $saveResult = FacadesStorage::disk($targetDisk)->put($targetPath, $fileContent);
            if ($saveResult === false) {
                throw new \Exception('文件保存到目标磁盘失败');
            }

            // 步骤6: 删除 local 临时文件
            FacadesStorage::disk('local')->delete($path);

            // 步骤7: 创建文件记录（记录目标磁盘路径）
            $oName = !empty($originalName) ? $originalName : basename($path);

            $fileModel = new FileFile;
            $fileModel->storage_disk = $targetDisk;
            $fileModel->path = $targetPath; // 使用新路径，不是临时路径
            $fileModel->re_type = $reType;
            $fileModel->re_id = $reId;
            $fileModel->o_name = $oName;
            $fileModel->fsize = $fsize;
            $fileModel->type1 = $extension;
            $fileModel->save();

            // 触发文件上传事件
            FileUploadedEvent::dispatch($fileModel);

            return $fileModel;
        } catch (\Throwable $e) {
            SystemLogService::exception('afile', $e, [
                'operation' => 'createFileFromPath',
                'path' => $path,
                'user_id' => $userId,
            ]);
            throw $e;
        }
    }

    /**
     * 更新图片的关联关系
     *
     * @param int $imageId 图片ID
     * @param string $reType 关联类型
     * @param int $reId 关联ID
     * @return bool 是否成功
     */
    public static function updateImageRelation(int $imageId, string $reType, int $reId): bool
    {
        $image = FileImg::find($imageId);
        if (!$image) {
            return false;
        }

        $image->re_type = $reType;
        $image->re_id = $reId;
        $image->status = 'linked'; // 标记为已关联
        $image->used_at = now();
        $image->save();

        return true;
    }

    /**
     * 移除图片的关联关系
     *
     * @param int $imageId 图片ID
     * @return bool 是否成功
     */
    public static function removeImageRelation(int $imageId): bool
    {
        $image = FileImg::find($imageId);
        if (!$image) {
            return false;
        }

        $image->re_type = '';
        $image->re_id = 0;
        $image->status = 'normal'; // 标记为正常（未关联）
        $image->save();

        return true;
    }

    /**
     * 下载文件
     *
     * @param  int  $fileId  文件ID
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|null 下载响应或 null（文件不存在）
     */
    public static function downloadFile(int $fileId): ?\Symfony\Component\HttpFoundation\StreamedResponse
    {
        try {
            $file = FileFile::find($fileId);
            if (! $file) {
                return null;
            }

            $disk = $file->storage_disk ?: StorageConfig::getStorage();
            return FacadesStorage::disk($disk)->download($file->path, $file->o_name);
        } catch (\Throwable $e) {
            SystemLogService::exception('afile', $e, [
                'operation' => 'downloadFile',
                'file_id' => $fileId,
            ]);
            throw $e;
        }
    }
}
