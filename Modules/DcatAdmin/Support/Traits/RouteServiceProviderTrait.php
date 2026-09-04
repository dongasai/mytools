<?php

namespace Modules\DcatAdmin\Support\Traits;

use Illuminate\Support\Facades\Route;
use Nwidart\Modules\Facades\Module;

/**
 * 路由服务提供者特征
 * 提供通用的路由映射方法
 */
trait RouteServiceProviderTrait
{
    /**
     * 定义Admin路由
     */
    protected function mapAdminRoutes(): void
    {
        $module = $this->getModuleName();
        $adminRouteFile = Module::getModulePath($module) . "/routes/admin.php";

        if (file_exists($adminRouteFile)) {
            Route::middleware(config('admin.route.middleware'))
                ->prefix(config('admin.route.prefix'))
                ->group($adminRouteFile);
        }
    }

    /**
     * 获取模块名称
     * 子类需要实现此方法或设置 $module 属性
     */
    protected function getModuleName(): string
    {
        if (property_exists($this, 'module')) {
            return $this->module;
        }

        if (property_exists($this, 'name')) {
            return $this->name;
        }

        if (property_exists($this, 'nameLower')) {
            return str_replace('module_', '', $this->nameLower);
        }

        // 从类名中提取模块名
        $className = class_basename($this);

        return str_replace(['RouteServiceProvider', 'Route'], '', $className);
    }

    /**
     * 定义Web路由
     */
    protected function mapWebRoutes(): void
    {
        $module = $this->getModuleName();
        $webRouteFile = Module::getModulePath($module) . "/routes/web.php";

        if (file_exists($webRouteFile)) {
            Route::middleware('web')->group($webRouteFile);
        }
    }

    /**
     * 定义API路由
     */
    protected function mapApiRoutes(): void
    {
        $module = $this->getModuleName();
        $apiRouteFile = Module::getModulePath($module) . "/routes/api.php";

        if (file_exists($apiRouteFile)) {
            Route::middleware('api')->prefix('api')->name('api.')->group($apiRouteFile);
        }
    }
}
