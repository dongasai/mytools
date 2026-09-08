<?php

namespace Modules\FeatureDbadmin\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Modules\DcatAdmin\Support\Traits\RouteServiceProviderTrait;

/**
 * FeatureDbadmin模块路由服务提供者
 */
class RouteServiceProvider extends ServiceProvider
{
    use RouteServiceProviderTrait;

    protected string $name = 'FeatureDbadmin';

    protected string $nameLower = 'featuredbadmin';

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
        // Admin后台路由
        $this->mapAdminRoutes();

        // API路由
        $this->mapApiRoutes();
    }
}