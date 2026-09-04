<?php

namespace Modules\DcatAdmin\DcatAdmin\Repositories;

use Dcat\Admin\Grid\Model;
use Dcat\Admin\Repositories\Repository;

class RouteName extends Repository
{
    private $allRoutes = null;

    /**
     * 获取所有路由数据（缓存）
     */
    private function getAllRoutes()
    {
        if ($this->allRoutes === null) {
            $this->allRoutes = [];
            $routes = \Illuminate\Support\Facades\Route::getRoutes();

            foreach ($routes->getRoutesByName() as $name => $route) {
                $action = $route->action;

                // 处理 controller 字段，确保为字符串类型
                $controller = '';
                if (isset($action['controller'])) {
                    if (is_string($action['controller'])) {
                        $controller = $action['controller'];
                    } elseif (is_object($action['controller'])) {
                        $controller = get_class($action['controller']);
                    }
                } elseif (isset($action['uses'])) {
                    if (is_string($action['uses'])) {
                        $controller = $action['uses'];
                    } elseif (is_object($action['uses'])) {
                        $controller = get_class($action['uses']);
                    }
                }

                $this->allRoutes[] = [
                    'as' => $name,
                    'prefix' => $action['prefix'] ?? '',
                    'namespace' => $action['namespace'] ?? '',
                    'controller' => $controller,
                    'uses' => $action['uses'] ?? '',
                ];
            }
        }

        return $this->allRoutes;
    }

    /**
     * 获取数据（支持搜索）
     */
    public function get(Model $model)
    {
        $list = $this->getAllRoutes();

        // 检查是否有搜索关键词
        $searchKeyword = request()->string('_search_')->value();
        if (!empty($searchKeyword)) {
            $list = $this->filterRoutes($list, $searchKeyword);
        }

        return $list;
    }

    /**
     * 搜索过滤路由
     */
    private function filterRoutes(array $routes, string $keyword): array
    {
        $keyword = strtolower($keyword);

        return array_filter($routes, function ($route) use ($keyword) {
            // 搜索路由名称
            if (!empty($route['as']) && stripos($route['as'], $keyword) !== false) {
                return true;
            }

            // 搜索控制器
            if (!empty($route['controller']) && stripos($route['controller'], $keyword) !== false) {
                return true;
            }

            // 搜索命名空间
            if (!empty($route['namespace']) && stripos($route['namespace'], $keyword) !== false) {
                return true;
            }

            // 搜索处理方法（确保是字符串）
            if (!empty($route['uses'])) {
                if (is_string($route['uses']) && stripos($route['uses'], $keyword) !== false) {
                    return true;
                }
            }

            // 搜索前缀
            if (!empty($route['prefix']) && stripos($route['prefix'], $keyword) !== false) {
                return true;
            }

            return false;
        });
    }
}
