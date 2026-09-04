<?php

namespace Modules\FeatureSms\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

/**
 * FeatureSms 路由服务提供者
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * 模块命名空间
     */
    protected string $moduleNamespace = 'Modules\\FeatureSms\\';

    /**
     * 定义路由
     */
    public function boot(): void
    {
        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->moduleNamespace . 'Api')
                ->group(module_path('FeatureSms', 'routes/api.php'));

            Route::middleware('web')
                ->namespace($this->moduleNamespace . 'Web')
                ->group(module_path('FeatureSms', 'routes/web.php'));

            // Admin 后台路由
            Route::middleware(['web', 'admin'])
                ->prefix('admin')
                ->name('admin.')
                ->namespace($this->moduleNamespace . 'DcatAdmin\\Controllers')
                ->group(module_path('FeatureSms', 'routes/admin.php'));
        });
    }
}
