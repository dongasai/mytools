<?php

namespace Modules\Demo5\Listeners;

use Modules\Base\Listeners\ModuleDispatcher;
use Modules\Demo5\Events\PostCreatedEvent;
use Modules\Demo5\Events\ModuleDemo5PostCreatedEvent;

/**
 * Demo5模块统一事件转发器
 *
 * 一个转发器处理Demo5模块所有Event的跨模块转发
 * 通过eventMap配置表映射多个Event(异步队列event)
 *
 * 未来可扩展:
 * - PostUpdatedEvent => ModuleDemo5PostUpdatedEvent
 * - PostDeletedEvent => ModuleDemo5PostDeletedEvent
 * - CommentCreatedEvent => ModuleDemo5CommentCreatedEvent
 */
class DispatchModuleDemo5 extends ModuleDispatcher
{
    /**
     * Event映射表
     *
     * 格式: [源Event => [目标Event, 字段列表]]
     */
    protected array $eventMap = [
        PostCreatedEvent::class => [
            'target' => ModuleDemo5PostCreatedEvent::class,
            'fields' => ['post'], // 只转发核心信息post
        ],

        // 未来扩展示例:
        // PostUpdatedEvent::class => [
        //     'target' => ModuleDemo5PostUpdatedEvent::class,
        //     'fields' => ['post'],
        // ],
        //
        // CommentCreatedEvent::class => [
        //     'target' => ModuleDemo5CommentCreatedEvent::class,
        //     'fields' => ['comment'],
        // ],
    ];
}