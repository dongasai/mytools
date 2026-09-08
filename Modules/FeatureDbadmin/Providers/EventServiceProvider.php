<?php

namespace Modules\FeatureDbadmin\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * FeatureDbadmin模块事件服务提供者
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected array $listen = [
        // 示例：
        // \Modules\FeatureDbadmin\Events\ExampleEvent::class => [
        //     \Modules\FeatureDbadmin\Listeners\ExampleListener::class,
        // ],
    ];
}