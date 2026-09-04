<?php

namespace Modules\AFile;

use Illuminate\Support\Facades\Storage;
use Modules\AFile\Logics\StorageConfig;
use Modules\AFile\Models\FileFile;
use Modules\AFile\Services\FileService;

/**
 * 文件辅助类（静态方法）
 *
 * 提供便捷的静态方法访问文件服务
 */
class File
{
    /**
     * 检查文件是否存在
     *
     * @param string $path 文件路径
     * @param string|null $disk 存储磁盘（默认使用配置的磁盘）
     * @return bool 是否存在
     */
    public static function fileExists(string $path, ?string $disk = null): bool
    {
        $disk = $disk ?: StorageConfig::getStorage();
        return Storage::disk($disk)->exists($path);
    }

    /**
     * 通过ID获取文件URL
     *
     * @param int $id 文件ID
     * @return string 文件URL
     */
    public static function getUrl4Id(int $id): string
    {
        $fileModel = FileFile::find($id);
        if (!$fileModel) {
            return '';
        }

        $disk = $fileModel->storage_disk ?: StorageConfig::getStorage();
        return Storage::disk($disk)->url($fileModel->path);
    }

    /**
     * 通过路径获取文件URL
     *
     * @param string $path 文件路径
     * @param string|null $disk 存储磁盘（默认使用配置的磁盘）
     * @return string 文件URL
     */
    public static function getUrl4Path(string $path, ?string $disk = null): string
    {
        $disk = $disk ?: StorageConfig::getStorage();
        return Storage::disk($disk)->url($path);
    }

    /**
     * 获取文件内容
     *
     * @param string $path 文件路径
     * @param string|null $disk 存储磁盘
     * @return string|null 文件内容
     */
    public static function getContent(string $path, ?string $disk = null): ?string
    {
        $disk = $disk ?: StorageConfig::getStorage();

        if (!self::fileExists($path, $disk)) {
            return null;
        }

        return Storage::disk($disk)->get($path);
    }

    /**
     * 删除文件
     *
     * @param int $id 文件ID
     * @return bool 是否成功
     */
    public static function delete4Id(int $id): bool
    {
        $fileService = new FileService();
        return $fileService->deleteFile($id);
    }
}