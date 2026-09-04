<?php

declare(strict_types=1);

namespace Modules\FeatureAi\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Modules\DcatAdmin\Support\Traits\RouteServiceProviderTrait;

/**
 * FeatureAi 模块 DcatAdmin 路由服务提供者
 */
class DcatAdminRouteServiceProvider extends ServiceProvider
{
    use RouteServiceProviderTrait;

    /**
     * 模块名称
     */
    protected string $name = 'FeatureAi';

    /**
     * 模块路由前缀
     */
    protected string $nameLower = 'module_featureai';

    /**
     * Boot the route service provider.
     */
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Register routes.
     */
    public function map(): void
    {
        $this->mapAdminRoutes();
    }
}