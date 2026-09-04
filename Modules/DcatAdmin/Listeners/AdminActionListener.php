<?php

namespace Modules\DcatAdmin\Listeners;

use Modules\Admin\Events\AdminActionEvent;
use Modules\Admin\Models\AdminLog;
use DLaravel\Helper\Logger;
use Illuminate\Support\Facades\Log;

/**
 * 管理员操作监听器
 */
class AdminActionListener
{
    /**
     * 处理事件
     */
    public function handle(AdminActionEvent $event): void
    {
        try {
            // 保存到数据库
            $this->saveToDatabase($event);

            // 记录到日志文件
            $this->logToFile($event);

            // 发送通知（如果需要）
            $this->sendNotificationIfNeeded($event);

        } catch (\Exception $e) {
            Logger::error('Admin action listener error: '.$e->getMessage(), [
                'event_data' => $event->getActionData(),
                'error' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * 保存到数据库
     */
    protected function saveToDatabase(AdminActionEvent $event): void
    {
        try {
            AdminLog::create([
                'admin_id' => $event->getAdminId(),
                'admin_name' => $event->getAdminName(),
                'action_type' => $event->getActionType(),
                'description' => $event->getDescription(),
                'data' => json_encode($event->getData(), JSON_UNESCAPED_UNICODE),
                'ip_address' => $event->getIpAddress(),
                'user_agent' => $event->getUserAgent(),
                'created_at' => $event->getTimestamp(),
            ]);
        } catch (\Exception $e) {
            Logger::error('Failed to save admin action to database: '.$e->getMessage());
        }
    }

    /**
     * 记录到日志文件
     */
    protected function logToFile(AdminActionEvent $event): void
    {
        $logData = [
            'admin_id' => $event->getAdminId(),
            'admin_name' => $event->getAdminName(),
            'action_type' => $event->getActionType(),
            'description' => $event->getDescription(),
            'ip_address' => $event->getIpAddress(),
            'timestamp' => $event->getTimestamp()?->toDateTimeString(),
        ];

        Log::channel('admin')->info('Admin Action: '.$event->getDescription(), $logData);
    }

    /**
     * 发送通知（如果需要）
     */
    protected function sendNotificationIfNeeded(AdminActionEvent $event): void
    {
        // 检查是否需要发送通知
        $criticalActions = [
            'DELETE',
            'MAINTENANCE',
            'PERMISSION_CHANGE',
            'SYSTEM_RESTART',
            'BACKUP',
            'RESTORE',
        ];

        if (in_array($event->getActionType(), $criticalActions)) {
            // 这里可以实现发送通知的逻辑
            // 例如：发送邮件、短信、推送等
            Log::channel('admin')->warning('Critical Admin Action: '.$event->getDescription(), [
                'admin_name' => $event->getAdminName(),
                'action_type' => $event->getActionType(),
                'ip_address' => $event->getIpAddress(),
                'timestamp' => $event->getTimestamp()?->toDateTimeString(),
            ]);
        }
    }
}
