<?php

namespace Modules\FeatureDbadmin\DcatAdmin\Controllers;

use Dcat\Admin\Layout\Content;
use Illuminate\Routing\Controller;
use Modules\FeatureDbadmin\Models\QueryHistory;
use Modules\FeatureDbadmin\Services\DatabaseService;

/**
 * FeatureDbadmin 仪表盘控制器
 *
 * 系统概览、统计信息展示
 */
class DashboardController extends Controller
{
    /**
     * 仪表盘首页
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content): Content
    {
        $stats = $this->getStats();

        return $content
            ->title('数据库管理员工具')
            ->description('系统概览')
            ->body(view('featuredbadmin::dashboard.index', [
                'stats' => $stats,
            ]));
    }

    /**
     * 获取统计数据
     *
     * @return array<string, mixed>
     */
    private function getStats(): array
    {
        $connections = DatabaseService::getConnections(false);
        $activeConnections = DatabaseService::getConnections(true);

        return [
            'connections_count' => count($connections),
            'active_connections_count' => count($activeConnections),
            'queries_count' => QueryHistory::count(),
            'today_queries_count' => QueryHistory::whereDate('created_at', today())->count(),
        ];
    }

    /**
     * 统计数据 JSON 接口
     *
     * 返回连接数量、查询次数、活跃连接等统计数据
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats()
    {
        $connections = DatabaseService::getConnections(false);
        $activeConnections = DatabaseService::getConnections(true);

        return response()->json([
            'connections' => count($connections),
            'active_connections' => count($activeConnections),
            'queries' => QueryHistory::count(),
            'today_queries' => QueryHistory::whereDate('created_at', today())->count(),
        ]);
    }
}
