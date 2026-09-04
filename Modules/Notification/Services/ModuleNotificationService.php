<?php

declare(strict_types=1);

namespace Modules\Notification\Services;

use Illuminate\Database\Eloquent\Model;
use Modules\Notification\Logics\TemplateLogic;
use Modules\Notification\Logics\NotificationLogic;
use Modules\Notification\Models\NotificationLog;
use Modules\Notification\Queues\SendNotificationQueue;

/**
 * 模块通知服务.
 *
 * 提供模块级别的通知发送接口，全静态方法，供其他模块直接调用。
 */
class ModuleNotificationService
{
    /**
     * 发送单个通知.
     *
     * 渲染模板、创建日志并推送到队列。
     *
     * @param string $notificationType 通知类型
     * @param string $channel 通知渠道
     * @param Model $notifiable 接收通知的对象
     * @param array<string, mixed> $data 通知数据
     * @return NotificationLog 创建的通知日志实例
     */
    public static function send(
        string $notificationType,
        string $channel,
        Model $notifiable,
        array $data = []
    ): NotificationLog {
        // 渲染模板（如果模板不存在则使用原始数据）
        $rendered = TemplateLogic::renderTemplate($notificationType, $channel, $data);

        // 合并渲染后的内容到数据（模板不存在时使用默认内容）
        $logData = array_merge($data, [
            'subject' => $rendered ? $rendered['subject'] : ($data['subject'] ?? null),
            'content' => $rendered ? $rendered['content'] : ($data['content'] ?? ''),
        ]);

        // 创建通知日志
        $log = NotificationLogic::createLog($notificationType, $channel, $notifiable, $logData);

        // 推送到队列
        SendNotificationQueue::dispatch($log->id);

        return $log;
    }

    /**
     * 批量发送通知.
     *
     * 为多个接收对象批量发送相同类型的通知。
     *
     * @param string $notificationType 通知类型
     * @param string $channel 通知渠道
     * @param array<int, Model> $notifiables 接收通知的对象数组
     * @param array<string, mixed> $data 通知数据
     * @return array<int, NotificationLog> 创建的通知日志实例数组
     */
    public static function sendBatch(
        string $notificationType,
        string $channel,
        array $notifiables,
        array $data = []
    ): array {
        // 渲染模板（如果模板不存在则使用原始数据）
        $rendered = TemplateLogic::renderTemplate($notificationType, $channel, $data);

        // 合并渲染后的内容到数据（模板不存在时使用默认内容）
        $logData = array_merge($data, [
            'subject' => $rendered ? $rendered['subject'] : ($data['subject'] ?? null),
            'content' => $rendered ? $rendered['content'] : ($data['content'] ?? ''),
        ]);

        // 批量创建通知日志
        $logs = NotificationLogic::createBatchLogs($notificationType, $channel, $notifiables, $logData);

        // 推送所有日志到队列
        foreach ($logs as $log) {
            SendNotificationQueue::dispatch($log->id);
        }

        return $logs;
    }

    /**
     * 获取通知统计.
     *
     * 获取指定通知类型的状态统计信息。
     *
     * @param string|null $notificationType 通知类型（null 表示统计所有类型）
     * @return array<string, int> 状态统计数组
     */
    public static function getStatistics(?string $notificationType = null): array
    {
        return NotificationLogic::getStatusStatistics($notificationType);
    }
}