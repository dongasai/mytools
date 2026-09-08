<?php

namespace Modules\FeatureDbadmin\Providers;

use Modules\ABase\Support\ServiceProvider;

/**
 * FeatureDbadmin 模块服务提供者
 *
 * 模块服务提供者
 */
class FeatureDbadminServiceProvider extends ServiceProvider
{
    protected string $name = 'FeatureDbadmin';

    protected string $nameLower = 'featuredbadmin';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->modulePath = dirname(__DIR__);
        parent::boot();
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        // 注册路由服务提供者
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        // 注册命令
    }

    /**
     * Register command Schedules.
     */
    protected function registerCommandSchedules(): void
    {
        //
    }
}