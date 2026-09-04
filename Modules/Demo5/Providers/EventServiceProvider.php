<?php

namespace Modules\Demo5\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Demo5\Events\PostCreatedEvent;
use Modules\Demo5\Events\ModuleDemo5PostCreatedEvent;
use Modules\Demo5\Listeners\LogPostCreatedListener;
use Modules\Demo5\Listeners\NotifyPostPublishedListener;
use Modules\Demo5\Listeners\DispatchModuleDemo5;

/**
 * Demo5 模块事件服务提供者
 *
 * 注册模块的事件与监听器映射关系
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * 事件监听器映射
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        // 模块内Event（完整参数）
        PostCreatedEvent::class => [
            LogPostCreatedListener::class,       // 同步处理模块内业务：日志记录
            NotifyPostPublishedListener::class,  // 异步处理通知（queue:event）
            DispatchModuleDemo5::class,          // 异步统一转发器（queue:event）
        ],

        // 跨模块Event（精简参数，其他模块监听）
        ModuleDemo5PostCreatedEvent::class => [
            // 其他模块在这里注册监听器，例如：
            // \Modules\User\Listeners\HandleDemo5PostCreated::class,
            // \Modules\Notification\Listeners\SendPostNotification::class,
        ],
    ];

    /**
     * 是否自动发现事件
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = true;

    /**
     * 配置邮箱验证事件监听器
     *
     * @return void
     */
    protected function configureEmailVerification(): void {}
}
