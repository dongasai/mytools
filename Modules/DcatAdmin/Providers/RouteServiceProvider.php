<?php

namespace Modules\DcatAdmin\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Modules\DcatAdmin\Support\Traits\RouteServiceProviderTrait;

/**
 * DcatAdmin模块路由服务提供者
 */
class RouteServiceProvider extends ServiceProvider
{
    use RouteServiceProviderTrait;

    protected string $name = 'DcatAdmin';

    protected string $nameLower = 'module_dcatadmin';

    /**
     * 定义应用的 routes
     */
    public function map(): void
    {
        $this->mapAdminRoutes();
    }
}
