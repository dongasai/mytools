<?php

namespace Modules\Demo5\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\Demo5\Events\PostCreatedEvent;

/**
 * 文章创建日志监听器（同步）
 *
 * 监听文章创建事件并记录日志
 * 模块内业务监听器，不实现ShouldQueue，同步处理
 */
class LogPostCreatedListener
{
    /**
     * 处理事件（同步）
     */
    public function handle(PostCreatedEvent $event): void
    {
        // 记录文章创建日志
        Log::info('文章创建事件', $event->getLogData());

        // 模块内业务处理：日志记录
        $this->logPostCreation($event);
    }

    /**
     * 记录文章创建详情
     */
    protected function logPostCreation(PostCreatedEvent $event): void
    {
        $message = $event->getDescription();

        if ($event->getUserId()) {
            $message .= " (用户ID: {$event->getUserId()})";
        }

        Log::channel('demo5')->info($message, [
            'post_id' => $event->getPostId(),
            'title' => $event->getPostTitle(),
            'status' => $event->getPostStatus(),
            'is_published' => $event->isPublished(),
        ]);
    }
}
