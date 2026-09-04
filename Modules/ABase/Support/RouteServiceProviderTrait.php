<?php

namespace Modules\ABase\Support;

use Illuminate\Support\Facades\Route;

trait RouteServiceProviderTrait
{
    /**
     * 注册模块路由
     */
    protected function registerModuleRoutes(): void
    {
        $this->registerWebRoutes();
        $this->registerApiRoutes();
        $this->registerAdminRoutes();
    }

    /**
     * 注册Web路由
     */
    protected function registerWebRoutes(): void
    {
        if (file_exists($this->modulePath . '/routes/web.php')) {
            Route::middleware('web')
                ->group($this->modulePath . '/routes/web.php');
        }
    }

    /**
     * 注册API路由
     */
    protected function registerApiRoutes(): void
    {
        if (file_exists($this->modulePath . '/routes/api.php')) {
            Route::middleware('api')
                ->prefix('api')
                ->group($this->modulePath . '/routes/api.php');
        }
    }

    /**
     * 注册后台路由
     */
    protected function registerAdminRoutes(): void
    {
        if (file_exists($this->modulePath . '/routes/admin.php')) {
            Route::middleware(['web', 'admin'])
                ->prefix('admin')
                ->group($this->modulePath . '/routes/admin.php');
        }
    }

    /**
     * 获取模块路径
     */
    protected function getModulePath(): string
    {
        return $this->modulePath ?? dirname(__DIR__, 2);
    }
}
