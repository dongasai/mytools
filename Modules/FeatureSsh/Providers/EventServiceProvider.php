<?php

namespace Modules\FeatureSsh\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * FeatureSsh模块事件服务提供者
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
        // \Modules\FeatureSsh\Events\ExampleEvent::class => [
        //     \Modules\FeatureSsh\Listeners\ExampleListener::class,
        // ],
    ];
}