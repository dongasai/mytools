<?php

declare(strict_types=1);

namespace Modules\Debug\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;

/**
 * 调试路由控制器
 *
 * 提供路由列表查看功能
 *
 * @package Modules\Debug\Http\Controllers
 */
class DebugRouteController extends Controller
{
    /**
     * 调试首页
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $tools = [
            ['name' => '路由列表', 'page' => 'routes', 'description' => '查看所有已注册的路由'],
            ['name' => '服务器信息', 'page' => 'server', 'description' => '查看 PHP 和服务器环境变量'],
            ['name' => 'ApiProto API 测试', 'page' => 'apiproto', 'description' => '测试 ApiProto 模块的 REST API'],
        ];

        return view('debug::index', compact('tools'));
    }

    /**
     * 显示服务器信息
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function server(Request $request)
    {
        $serverVars = collect($request->server())->sortKeys();

        $scheme = $request->getScheme();
        $isSecure = $request->isSecure();
        $https = $request->server('HTTPS', 'not set');
        $serverPort = $request->server('SERVER_PORT', 'not set');
        $httpHost = $request->server('HTTP_HOST', 'not set');

        return view('debug::server', compact('serverVars', 'scheme', 'isSecure', 'https', 'serverPort', 'httpHost'));
    }

    /**
     * 显示路由列表
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function routes(Request $request)
    {
        $filter = $request->input('filter', '');
        $method = $request->input('method', '');
        $prefix = $request->input('prefix', '');

        $routes = collect(Route::getRoutes());

        $routeList = $routes->map(function ($route) {
            $uri = $route->uri();
            $prefix = $this->extractPrefix($uri);

            return [
                'method' => implode('|', $route->methods()),
                'uri' => $uri,
                'prefix' => $prefix,
                'name' => $route->getName() ?? '',
                'action' => is_string($action = $route->getAction('uses')) ? $action : 'Closure',
                'middleware' => implode(', ', $route->gatherMiddleware()),
            ];
        });

        // 提取所有前缀用于筛选下拉
        $allPrefixes = $routeList->pluck('prefix')->unique()->sort()->values()->filter();

        // 过滤
        if ($prefix) {
            $routeList = $routeList->filter(function ($item) use ($prefix) {
                return $item['prefix'] === $prefix;
            });
        }

        if ($filter) {
            $routeList = $routeList->filter(function ($item) use ($filter) {
                return stripos($item['uri'], $filter) !== false
                    || stripos($item['name'], $filter) !== false
                    || stripos($item['action'], $filter) !== false;
            });
        }

        if ($method) {
            $routeList = $routeList->filter(function ($item) use ($method) {
                return stripos($item['method'], strtoupper($method)) !== false;
            });
        }

        $routeList = $routeList->values();
        $totalCount = $routes->count();
        $filteredCount = $routeList->count();

        // 按前缀分组
        $groupedRoutes = $routeList->groupBy('prefix')->sortKeys();

        return view('debug::routes', compact('routeList', 'groupedRoutes', 'filter', 'method', 'prefix', 'allPrefixes', 'totalCount', 'filteredCount'));
    }

    /**
     * 从URI中提取前缀
     *
     * @param string $uri
     * @return string
     */
    private function extractPrefix(string $uri): string
    {
        // 移除开头的 / 或 {
        $uri = ltrim($uri, '/');

        // 如果以 { 开头（路由参数），返回空
        if (str_starts_with($uri, '{')) {
            return '';
        }

        // 提取第一段作为前缀
        $segments = explode('/', $uri);
        return $segments[0] ?? '';
    }
}