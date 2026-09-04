<?php

declare(strict_types=1);

namespace Modules\Notification\Queues;

use Modules\Notification\Models\NotificationLog;
use Modules\Notification\Logics\NotificationLogic;
use Modules\Notification\Services\NotificationService;
use Modules\Application\Services\SystemLogService;
use DLaravel\Queue\QueueJob;

/**
 * 发送通知队列任务类.
 *
 * 该类负责异步处理通知的发送，支持失败重试和超时控制。
 */
class SendNotificationQueue extends QueueJob
{
    /**
     * 通知日志ID.
     *
     * @var int
     */
    protected int $notificationId;

    /**
     * 最大重试次数.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * 超时时间（秒）.
     *
     * @var int
     */
    public $timeout = 60;

    /**
     * 创建队列任务.
     *
     * @param int $notificationId 通知日志ID
     */
    public function __construct(int $notificationId)
    {
        $this->notificationId = $notificationId;
        parent::__construct(['notification_id' => $notificationId]);
    }

    /**
     * 执行队列任务.
     *
     * @return bool
     */
    public function run(): bool
    {
        // 获取通知日志
        $log = NotificationLog::findOrFail($this->notificationId);

        // 处理通知发送
        NotificationService::handleNotification($log);

        return true;
    }

    /**
     * 获取任务数据.
     *
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        $log = NotificationLog::findOrFail($this->notificationId);

        return [
            'notification_id' => $log->id,
            'notification_data' => $log->toArray(),
        ];
    }

    /**
     * 处理失败的任务.
     *
     * @param \Throwable $exception 异常实例
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        $log = NotificationLog::findOrFail($this->notificationId);

        NotificationLogic::markAsFailed($log, $exception->getMessage());

        SystemLogService::exception('notification', $exception, [
            'operation' => 'queueSendNotification',
            'notification_id' => $this->notificationId,
            'queue' => 'notification',
        ]);
    }

    /**
     * 获取任务显示名称.
     *
     * @return string
     */
    public function displayName(): string
    {
        return 'SendNotificationQueue:' . $this->notificationId;
    }
}