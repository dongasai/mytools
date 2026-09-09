<?php

namespace Modules\FeatureDbadmin\DcatAdmin\Controllers;

use Illuminate\Http\Request;
use Modules\DcatAdmin\DcatAdmin\AdminController;
use Modules\FeatureDbadmin\Models\QueryHistory;
use Modules\FeatureDbadmin\Services\DatabaseService;

/**
 * FeatureDbadmin 仪表盘 API 控制器
 *
 * 提供仪表盘统计数据 API
 */
class DashboardController extends AdminController
{
    /**
     * 统计数据 JSON 接口
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

    /**
     * 查询历史 JSON 接口
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function queryHistory(Request $request)
    {
        $limit = $request->get('limit', 10);

        $histories = QueryHistory::orderBy('executed_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($history) {
                return [
                    'id' => $history->id,
                    'connection_name' => $history->connection_name,
                    'sql_query' => $history->sql_query,
                    'query_type' => $history->query_type,
                    'execution_time' => $history->execution_time,
                    'status' => $history->status,
                    'executed_at' => $history->executed_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'data' => $histories,
        ]);
    }
}
