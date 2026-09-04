<?php

namespace Modules\AFile\Logics;

use Illuminate\Support\Facades\Storage;

/**
 * 文件基础逻辑类
 *
 * 提供文件操作的底层逻辑
 */
class FileBaseLogic
{
    /**
     * 获取文件内容
     *
     * @param  string  $name  文件名
     * @param  string|null  $disk  存储磁盘
     * @return string 文件内容
     */
    public function getPath($name, $disk = null)
    {
        $src = Storage::disk($disk)->get($name);

        return $src;
    }

    /**
     * 获取文件可见性
     *
     * @param  string  $path  文件路径
     * @return string 文件可见性
     */
    public function getVisibility($path)
    {
        $disk = config('admin.upload.disk');

        if (config("filesystems.disks.{$disk}")) {
            $src = Storage::disk($disk)->getVisibility($path);
        }

        return $src;
    }

    /**
     * 获取文件的完整物理路径
     *
     * @param  string  $path  文件路径
     * @return string 完整物理路径
     */
    public function prefixPath($path)
    {
        $disk = config('admin.upload.disk');

        if (config("filesystems.disks.{$disk}")) {
            /**
             * @var \Illuminate\Filesystem\FilesystemAdapter $storage
             */
            $src = Storage::disk($disk)->path($path);
        }

        return $src;
    }

    /**
     * 检查文件是否存在
     *
     * @param  string  $path  文件路径
     * @return bool 是否存在
     */
    public function fileExists($path)
    {
        $disk = config('admin.upload.disk');

        if (config("filesystems.disks.{$disk}")) {
            /**
             * @var \Illuminate\Filesystem\FilesystemAdapter $storage
             */
            $src = Storage::disk($disk)->fileExists($path);
        }

        return $src;
    }
}
