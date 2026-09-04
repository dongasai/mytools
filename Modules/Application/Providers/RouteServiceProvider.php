<?php

declare(strict_types=1);

namespace Modules\Application\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Modules\DcatAdmin\Support\Traits\RouteServiceProviderTrait;

/**
 * Application模块DcatAdmin路由服务提供者
 */
class RouteServiceProvider extends ServiceProvider
{
    use RouteServiceProviderTrait;

    protected string $name = 'Application';

    protected string $nameLower = 'module_application';

    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     */
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     */
    public function map(): void
    {
        // 注册Admin后台路由
        $this->mapAdminRoutes();
    }
}