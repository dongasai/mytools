<?php

namespace Modules\AFile\Listeners;

use Modules\AFile\Events\MarkFileUsedEvent;

/**
 * 标记文件已使用监听器
 *
 * 仅用于扩展其他模块行为，不包含业务逻辑
 */
class MarkFileUsedListener
{
    /**
     * 处理事件（不包含业务逻辑）
     *
     * @param  MarkFileUsedEvent  $event
     * @return void
     */
    public function handle(MarkFileUsedEvent $event): void
    {
        // FileService::markFileAsUsed已在触发事件前调用
        // Listener仅用于扩展其他模块行为（如日志记录、通知等）
        // 不包含任何数据库操作业务逻辑
    }
}