<?php

declare(strict_types=1);

namespace Modules\AFile\Jobs;

use DLaravel\Queue\QueueJob;
use Illuminate\Support\Facades\Log;
use Modules\AFile\Models\FileFile;
use Modules\AFile\Models\FileImg;

/**
 * 标记悬空文件任务
 *
 * 每10分钟执行，标记上传1小时后仍未使用的文件为dangling状态
 */
class MarkDanglingFilesJob extends QueueJob
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
    public $timeout = 300;

    /**
     * 执行任务
     *
     * @return bool
     */
    public function run(): bool
    {
        $oneHourAgo = now()->subHour();

        // 处理file_files表 - 标记未使用的文件为悬空状态
        $filesCount = FileFile::where('status', 'normal')
            ->whereNull('used_at')
            ->where('created_at', '<', $oneHourAgo)
            ->update([
                'status' => 'dangling',
                'dangling_at' => now(),
            ]);

        // 处理file_imgs表 - 标记未使用的图片为悬空状态
        $imagesCount = FileImg::where('status', 'normal')
            ->whereNull('used_at')
            ->where('created_at', '<', $oneHourAgo)
            ->update([
                'status' => 'dangling',
                'dangling_at' => now(),
            ]);

        // 记录日志
        Log::info('悬空文件标记完成', [
            'files_count' => $filesCount,
            'images_count' => $imagesCount,
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
            'task' => 'mark_dangling_files',
        ];
    }
}