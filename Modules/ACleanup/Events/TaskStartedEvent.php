<?php

namespace Modules\AClean\Events;

use Modules\AClean\Models\CleanupTask;

/**
 * 清理任务开始事件
 */
class TaskStartedEvent
{
    /**
     * 清理任务实例
     */
    public readonly CleanupTask $task;

    /**
     * @param CleanupTask $task 清理任务实例
     */
    public function __construct(CleanupTask $task)
    {
        $this->task = $task;
    }
}