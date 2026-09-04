<?php

namespace Modules\Application\DcatAdmin\Repositories;

use Dcat\Admin\Grid;
use Dcat\Admin\Repositories\Repository;

/**
 * 所有的路由
 */
class Route extends Repository
{
    /**
     * 合并RouteName功能 - 获取命名路由
     */
    public function getNamedRoutes()
    {
        $list = [];
        $routes = \Illuminate\Support\Facades\Route::getRoutes();
        foreach ($routes->getRoutesByName() as $route) {
            $list[] = $route->action;
        }

        return $list;
    }

    public function get(Grid\Model $model)
    {
        $list = [];

        $routes = \Illuminate\Support\Facades\Route::getRoutes();
        foreach ($routes->getRoutes() as $route) {
            $list[] = $route->action;
        }

        return $list;
    }
}
