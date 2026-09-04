<?php

declare(strict_types=1);

namespace Modules\Notification\Logics;

use Illuminate\Database\Eloquent\Model;
use Modules\Notification\Models\NotificationLog;
use Modules\Notification\Enums\NOTIFICATION_STATUS;

/**
 * 通知逻辑类.
 *
 * 提供通知日志创建和统计相关的静态方法。
 */
class NotificationLogic
{
    /**
     * 创建单个通知日志.
     *
     * 根据通知类型、渠道、接收对象和数据创建通知日志记录。
     *
     * @param string $notificationType 通知类型
     * @param string $channel 通知渠道
     * @param Model $notifiable 接收通知的对象
     * @param array<string, mixed> $data 通知数据
     * @param string $status 初始状态（默认为 pending）
     * @return NotificationLog 创建的通知日志实例
     */
    public static function createLog(
        string $notificationType,
        string $channel,
        Model $notifiable,
        array $data,
        string $status = 'pending'
    ): NotificationLog {
        return NotificationLog::create([
            'notification_type' => $notificationType,
            'channel' => $channel,
            'notifiable_type' => $notifiable->getMorphClass(),
            'notifiable_id' => $notifiable->getKey(),
            'status' => $status,
            'data' => $data,
        ]);
    }

    /**
     * 批量创建通知日志.
     *
     * 为多个接收对象批量创建相同通知类型的日志记录。
     *
     * @param string $notificationType 通知类型
     * @param string $channel 通知渠道
     * @param array<int, Model> $notifiables 接收通知的对象数组
     * @param array<string, mixed> $data 通知数据
     * @return array<int, NotificationLog> 创建的通知日志实例数组
     */
    public static function createBatchLogs(
        string $notificationType,
        string $channel,
        array $notifiables,
        array $data
    ): array {
        $logs = [];

        foreach ($notifiables as $notifiable) {
            $logs[] = self::createLog($notificationType, $channel, $notifiable, $data);
        }

        return $logs;
    }

    /**
     * 获取通知状态统计.
     *
     * 统计指定通知类型或所有通知的状态分布情况。
     *
     * @param string|null $notificationType 通知类型（null 表示统计所有类型）
     * @return array<string, int> 状态统计数组 ['pending' => count, 'sending' => count, ...]
     */
    public static function getStatusStatistics(?string $notificationType = null): array
    {
        $query = NotificationLog::query();

        if ($notificationType !== null) {
            $query->where('notification_type', $notificationType);
        }

        $statistics = [];

        foreach (NOTIFICATION_STATUS::cases() as $status) {
            $statistics[$status->value] = $query->clone()
                ->where('status', $status->value)
                ->count();
        }

        return $statistics;
    }

    /**
     * 标记通知为发送中状态.
     *
     * @param NotificationLog $log 通知日志实例
     * @return bool 更新结果
     */
    public static function markAsSending(NotificationLog $log): bool
    {
        return $log->update([
            'status' => NOTIFICATION_STATUS::SENDING->value,
        ]);
    }

    /**
     * 标记通知为已发送状态.
     *
     * @param NotificationLog $log 通知日志实例
     * @return bool 更新结果
     */
    public static function markAsSent(NotificationLog $log): bool
    {
        return $log->update([
            'status' => NOTIFICATION_STATUS::SENT->value,
            'sent_at' => now(),
        ]);
    }

    /**
     * 标记通知为失败状态.
     *
     * @param NotificationLog $log 通知日志实例
     * @param string $errorMessage 错误信息
     * @return bool 更新结果
     */
    public static function markAsFailed(NotificationLog $log, string $errorMessage): bool
    {
        return $log->update([
            'status' => NOTIFICATION_STATUS::FAILED->value,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * 增加通知重试次数.
     *
     * @param NotificationLog $log 通知日志实例
     * @return int 更新后的重试次数
     */
    public static function incrementRetry(NotificationLog $log): int
    {
        return $log->increment('retry_count');
    }
}