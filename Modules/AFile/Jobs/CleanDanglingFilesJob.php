<?php

declare(strict_types=1);

namespace Modules\AFile\Jobs;

use DLaravel\Queue\QueueJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\AFile\Models\FileFile;
use Modules\AFile\Models\FileImg;

/**
 * 清理悬空文件任务
 *
 * 每小时执行，清理悬空超过24小时的文件
 */
class CleanDanglingFilesJob extends QueueJob
{
    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 3;

    /**
     * 任务超时时间（秒）
     *
     * @var int
     */
    public $timeout = 600;

    /**
     * 执行任务
     *
     * @return bool
     */
    public function run(): bool
    {
        $twentyFourHoursAgo = now()->subHours(24);

        $filesDeleted = 0;
        $imagesDeleted = 0;

        // 分批删除悬空文件（使用chunkById避免内存溢出）
        FileFile::where('status', 'dangling')
            ->where('dangling_at', '<', $twentyFourHoursAgo)
            ->chunkById(100, function ($danglingFiles) use (&$filesDeleted) {
                foreach ($danglingFiles as $file) {
                    // 删除物理文件
                    if ($file->storage_disk && $file->path) {
                        Storage::disk($file->storage_disk)->delete($file->path);
                    }
                    // 删除数据库记录（物理删除）
                    $file->forceDelete();
                    $filesDeleted++;
                }
            });

        // 分批删除悬空图片（使用chunkById避免内存溢出）
        FileImg::where('status', 'dangling')
            ->where('dangling_at', '<', $twentyFourHoursAgo)
            ->chunkById(100, function ($danglingImages) use (&$imagesDeleted) {
                foreach ($danglingImages as $image) {
                    // 删除物理文件
                    if ($image->storage_disk && $image->path) {
                        Storage::disk($image->storage_disk)->delete($image->path);
                    }
                    // 删除数据库记录（物理删除）
                    $image->forceDelete();
                    $imagesDeleted++;
                }
            });

        // 记录日志
        Log::info('悬空文件清理完成', [
            'files_deleted' => $filesDeleted,
            'images_deleted' => $imagesDeleted,
            'execute_time' => now()->toDateTimeString(),
        ]);

        return true;
    }

    /**
     * 获取任务数据
     *
     * @return array
     */
    public function payload(): array
    {
        return [
            'task' => 'clean_dangling_files',
        ];
    }
}