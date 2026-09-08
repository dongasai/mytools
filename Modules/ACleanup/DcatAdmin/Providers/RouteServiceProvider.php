<?php

namespace Modules\AClean\DcatAdmin\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Modules\DcatAdmin\Support\Traits\RouteServiceProviderTrait;

/**
 * AClean\DcatAdmin 模块路由服务提供者
 */
class RouteServiceProvider extends ServiceProvider
{
    use RouteServiceProviderTrait;

    protected string $name = 'AClean';

    // module_开头,只有一个`_`
    protected string $nameLower = 'module_acleanadmin';

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
        // Admin模块不处理其他路由，只处理 Admin 路由
        $this->mapAdminRoutes();
    }
}
