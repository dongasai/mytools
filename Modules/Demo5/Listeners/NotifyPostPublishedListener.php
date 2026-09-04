<?php

namespace Modules\Demo5\Listeners;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Modules\Demo5\Events\PostCreatedEvent;

/**
 * 文章发布通知监听器（异步）
 *
 * 监听文章创建事件，如果是已发布状态则发送通知
 * 模块内通知监听器，异步处理(queue:event)
 */
class NotifyPostPublishedListener implements ShouldQueue
{
    use Queueable;


    /**
     * 最大尝试次数
     */
    public int $tries = 3;

    /**
     * 处理事件（异步）
     */
    public function handle(PostCreatedEvent $event): void
    {
        // 只处理已发布的文章
        if (! $event->isPublished()) {
            return;
        }

        // 记录通知日志
        Log::info('文章发布通知已发送', [
            'post_id' => $event->getPostId(),
            'post_title' => $event->getPostTitle(),
            'user_id' => $event->getUserId(),
            'published_at' => $event->getCreatedAt()->toDateTimeString(),
        ]);

        // 这里可以添加实际的通知逻辑：
        // - 发送邮件给订阅者
        // - 推送通知到移动端
        // - 发送到社交媒体
        // - 更新RSS源
    }
}
