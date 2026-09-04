<?php

namespace Modules\AFile\Listeners;

use Modules\AFile\Events\MarkImageUsedEvent;

/**
 * 标记图片已使用监听器
 *
 * 说明:Listener用于扩展其他模块行为,不包含业务逻辑
 * 业务逻辑已在FileService::markImageAsUsed()中完成
 */
class MarkImageUsedListener
{
    /**
     * 处理事件
     *
     * @param  MarkImageUsedEvent  $event
     * @return void
     */
    public function handle(MarkImageUsedEvent $event): void
    {
        // Listener仅用于扩展其他模块行为
        // 所有数据库操作已在FileService::markImageAsUsed()中完成
        // 此处不包含任何业务逻辑
    }
}