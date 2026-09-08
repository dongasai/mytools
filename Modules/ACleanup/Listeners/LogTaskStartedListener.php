<?php

namespace Modules\AClean\Listeners;

use Modules\AClean\Events\TaskStartedEvent;
use Illuminate\Support\Facades\Log;

/**
 * 记录任务开始监听器
 */
class LogTaskStartedListener
{
    /**
     * 处理事件
     */
    public function handle(TaskStartedEvent $event): void
    {
        Log::info('清理任务开始', [
            'task_id' => $event->task->id,
            'task_name' => $event->task->task_name,
            'plan_id' => $event->task->plan_id,
            'started_at' => $event->task->started_at,
        ]);
    }
}