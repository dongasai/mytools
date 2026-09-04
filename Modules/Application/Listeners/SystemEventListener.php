<?php

namespace Modules\Application\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\Application\Events\ConfigChangedEvent;
use Modules\Application\Events\SystemLogCreatedEvent;
use Modules\Application\Events\ViewConfigChangedEvent;

/**
 * 系统事件监听器
 */
class SystemEventListener
{
    /**
     * 处理配置变更事件
     */
    public function handleConfigChanged(ConfigChangedEvent $event): void
    {
        try {
            // 记录日志
            Log::info('配置变更', [
                'keyname' => $event->keyname,
                'old_value' => $event->oldValue,
                'new_value' => $event->newValue,
                'admin_id' => $event->adminId,
            ]);

            // 这里可以添加其他处理逻辑，如发送通知等
        } catch (\Exception $e) {
            Log::error('处理配置变更事件失败', [
                'error' => $e->getMessage(),
                'keyname' => $event->keyname,
            ]);
        }
    }

    /**
     * 处理视图配置变更事件
     */
    public function handleViewConfigChanged(ViewConfigChangedEvent $event): void
    {
        try {
            // 记录日志
            Log::info('视图配置变更', [
                'id' => $event->id,
                'title' => $event->title,
                'old_params' => $event->oldParams,
                'new_params' => $event->newParams,
                'admin_id' => $event->adminId,
            ]);

            // 这里可以添加其他处理逻辑，如发送通知等
        } catch (\Exception $e) {
            Log::error('处理视图配置变更事件失败', [
                'error' => $e->getMessage(),
                'id' => $event->id,
            ]);
        }
    }

    /**
     * 处理系统日志创建事件
     */
    public function handleSystemLogCreated(SystemLogCreatedEvent $event): void
    {
        try {
            // 对于高级别的日志，可以进行特殊处理
            if (in_array($event->level, ['error', 'critical', 'alert', 'emergency'])) {
                // 记录到特殊日志
                Log::channel('emergency')->error('高级别系统日志', [
                    'id' => $event->id,
                    'type' => $event->type,
                    'level' => $event->level,
                    'message' => $event->message,
                    'context' => $event->context,
                    'user_id' => $event->userId,
                ]);

                // 这里可以添加其他处理逻辑，如发送通知等
            }
        } catch (\Exception $e) {
            \DLaravel\Helper\Logger::exception('处理系统日志创建事件失败', $e, [
                'id' => $event->id,
            ]);
        }
    }

    /**
     * 注册监听器
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events): void
    {
        $events->listen(
            ConfigChangedEvent::class,
            [SystemEventListener::class, 'handleConfigChanged']
        );

        $events->listen(
            ViewConfigChangedEvent::class,
            [SystemEventListener::class, 'handleViewConfigChanged']
        );

        $events->listen(
            SystemLogCreatedEvent::class,
            [SystemEventListener::class, 'handleSystemLogCreated']
        );
    }
}
