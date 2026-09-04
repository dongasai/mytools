<?php

namespace Modules\ABase\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * ABase 模块事件服务提供者
 *
 * 基础工具中台模块，暂无事件和监听器
 * 保留服务提供者以备后续扩展
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * 事件监听器映射
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [];

    /**
     * 是否自动发现事件
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = true;

    /**
     * 注册事件
     *
     * @return void
     */
    public function boot(): void
    {
        parent::boot();
    }
}
