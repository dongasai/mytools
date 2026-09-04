<?php

namespace Modules\DcatAdmin\Support\Providers;

use Illuminate\Support\Facades\Route;

/**
 * 路由服务提供者特征
 *
 * @mixin \Illuminate\Routing\Router
 */
trait AdminRouteServiceProvider
{
    /**
     * 定义 "admin" 路由
     */
    protected function mapAdminRoutes(): void
    {
        Route::middleware(config('admin.route.middleware'))
            ->prefix(config('admin.route.prefix'))
            ->group(module_path('Admin', 'routes/admin.php'));
    }
}
