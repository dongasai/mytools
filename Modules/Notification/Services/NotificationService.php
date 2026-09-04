<?php

declare(strict_types=1);

namespace Modules\Notification\Services;

use Modules\Notification\Logics\NotificationLogic;
use Modules\Notification\Models\NotificationLog;
use Modules\Notification\Models\NotificationTemplate;
use Modules\Application\Services\SystemLogService;
use Illuminate\Support\Facades\Log;

/**
 * 通知服务.
 *
 * 处理通知发送的核心服务，负责队列任务执行、钩子管理和模板管理。
 */
class NotificationService
{
    /**
     * 处理通知发送.
     *
     * 由队列任务调用，执行实际的通知发送流程。
     *
     * @param NotificationLog $log 通知日志实例
     * @return void
     */
    public static function handleNotification(NotificationLog $log): void
    {
        // 标记为发送中
        NotificationLogic::markAsSending($log);

        // 准备数据
        $data = [
            'notification_type' => $log->notification_type,
            'channel' => $log->channel,
            'notifiable' => $log->notifiable,
            'data' => $log->data,
        ];

        // 执行发送前钩子
        $data = HookManager::executeBeforeSend($data);

        // 根据渠道发送（Phase 5 实现具体逻辑）
        $result = self::sendByChannel($log->channel, $data);

        if ($result) {
            NotificationLogic::markAsSent($log);
            HookManager::executeAfterSend($data, true);
        } else {
            NotificationLogic::markAsFailed($log, 'Channel send failed');
            HookManager::executeAfterSend($data, false);
        }
    }

    /**
     * 根据渠道发送通知.
     *
     * Phase 5 实现具体渠道发送逻辑。
     * database 渠道已实现，其他渠道待实现。
     *
     * @param string $channel 通知渠道
     * @param array<string, mixed> $data 通知数据
     * @return bool 发送结果
     */
    protected static function sendByChannel(string $channel, array $data): bool
    {
        // database 渠道：存储到 notifications 表
        if ($channel === 'database') {
            return self::sendToDatabase($data);
        }

        // TODO: Phase 5 实现其他渠道发送逻辑
        // mail: 需要邮件服务配置
        // sms: 需要短信服务接口
        // push: 需要推送服务接口
        Log::info('渠道发送未实现', [
            'channel' => $channel,
            'data' => $data,
        ]);

        return false;
    }

    /**
     * 发送到 database 渠道.
     *
     * 将通知数据存储到 Laravel notifications 表。
     *
     * @param array<string, mixed> $data 通知数据
     * @return bool 发送结果
     */
    protected static function sendToDatabase(array $data): bool
    {
        try {
            // 获取接收对象
            $notifiable = $data['notifiable'] ?? null;

            if (!$notifiable) {
                Log::error('通知发送失败：缺少接收对象');
                HookManager::executeOnError($data, new \Exception('缺少接收对象'));
                return false;
            }

            // 构建通知数据
            $notificationData = [
                'notification_type' => $data['notification_type'] ?? 'Unknown',
                'channel' => 'database',
                'data' => $data['data'] ?? [],
            ];

            // 存储到 notifications 表（Laravel 原生）
            $notifiable->notifications()->create([
                'id' => uuid_create(UUID_TYPE_RANDOM),
                'type' => $notificationData['notification_type'],
                'notifiable_id' => $notifiable->id,
                'notifiable_type' => get_class($notifiable),
                'data' => json_encode($notificationData['data']),
                'read_at' => null,
            ]);

            Log::info('通知已存储到 database', [
                'notifiable_type' => get_class($notifiable),
                'notifiable_id' => $notifiable->id,
                'data' => $notificationData['data'],
            ]);

            return true;
        } catch (\Exception $e) {
            SystemLogService::exception('notification', $e, [
                'operation' => 'sendToDatabase',
                'channel' => 'database',
                'data' => $data,
            ]);
            HookManager::executeOnError($data, $e);
            return false;
        }
    }

    /**
     * 获取模板.
     *
     * 根据ID获取通知模板实例。
     *
     * @param int $id 模板ID
     * @return NotificationTemplate|null 模板实例或null
     */
    public static function getTemplate(int $id): ?NotificationTemplate
    {
        return NotificationTemplate::find($id);
    }

    /**
     * 创建模板.
     *
     * 创建新的通知模板。
     *
     * @param array<string, mixed> $data 模板数据
     * @return NotificationTemplate 创建的模板实例
     */
    public static function createTemplate(array $data): NotificationTemplate
    {
        return NotificationTemplate::create($data);
    }

    /**
     * 更新模板.
     *
     * 更新指定ID的模板数据。
     *
     * @param int $id 模板ID
     * @param array<string, mixed> $data 更新数据
     * @return bool 更新是否成功
     */
    public static function updateTemplate(int $id, array $data): bool
    {
        $template = self::getTemplate($id);

        if ($template === null) {
            return false;
        }

        return $template->update($data);
    }

    /**
     * 删除模板.
     *
     * 删除指定ID的模板。
     *
     * @param int $id 模板ID
     * @return bool 删除是否成功
     */
    public static function deleteTemplate(int $id): bool
    {
        $template = self::getTemplate($id);

        if ($template === null) {
            return false;
        }

        return $template->delete();
    }
}