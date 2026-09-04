<?php

namespace Modules\Demo5\Providers;

use Modules\ABase\Support\ServiceProvider;

/**
 * Demo5 模块服务提供者
 */
class Demo5ServiceProvider extends ServiceProvider
{
    protected string $name = 'Demo5';

    protected string $nameLower = 'module_demo5';

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
        $this->commands([
            \Modules\Demo5\Commands\GenerateDemoDataCommand::class,
        ]);
    }

    /**
     * Register command Schedules.
     */
    protected function registerCommandSchedules(): void
    {
        //
    }





    }
