<?php

declare(strict_types=1);

namespace Modules\Notification\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Modules\DcatAdmin\Support\Traits\RouteServiceProviderTrait;

/**
 * 通知模块路由服务提供者.
 *
 * 负责加载通知模块的后台路由配置，注册 DcatAdmin 管理路由。
 * 使用 RouteServiceProviderTrait 自动处理路由前缀和中间件配置。
 *
 * @package Modules\Notification\Providers
 */
class RouteServiceProvider extends ServiceProvider
{
    use RouteServiceProviderTrait;

    protected string $name = 'Notification';

    protected string $nameLower = 'module_notification';

    /**
     * 定义路由配置.
     *
     * @return void
     */
    public function map(): void
    {
        // Notification 模块仅支持Admin路由（无Web/API前端控制器）
        $this->mapAdminRoutes();
    }
}