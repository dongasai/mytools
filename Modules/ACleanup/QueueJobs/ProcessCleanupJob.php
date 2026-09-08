<?php

declare(strict_types=1);

namespace Modules\AClean\QueueJobs;

use DLaravel\Queue\QueueJob;
use Modules\AClean\Models\CleanupTask;
use Modules\AClean\Services\ACleanService;

/**
 * 处理清理任务的队列作业
 */
class ProcessCleanupJob extends QueueJob
{
    /**
     * 任务重试次数
     */
    public $tries = 3;

    /**
     * 任务超时时间（秒）
     */
    public int $timeout = 3600;

    /**
     * 清理任务实例
     */
    public readonly CleanupTask $task;

    /**
     * @param CleanupTask $task 清理任务实例
     */
    public function __construct(CleanupTask $task)
    {
        parent::__construct(['task_id' => $task->id]);
        $this->task = $task;
    }

    /**
     * 执行队列作业
     */
    public function run(): bool
    {
        $cleanupService = app(ACleanService::class);
        $cleanupService->processTask($this->task);
        return true;
    }

    /**
     * 获取任务负载信息
     */
    public function payload(): array
    {
        return [
            'task' => 'process_cleanup',
            'task_id' => $this->task->id,
        ];
    }

    /**
     * 处理任务失败
     */
    public function failed(\Throwable $exception): void
    {
        $this->task->update([
            'status' => 5, // 已失败
            'error_message' => $exception->getMessage(),
            'completed_at' => now(),
        ]);
    }
}
