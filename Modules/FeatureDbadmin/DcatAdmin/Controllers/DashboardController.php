<?php

namespace Modules\FeatureDbadmin\DcatAdmin\Controllers;

use Dcat\Admin\Layout\Content;
use Illuminate\Http\Request;
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
     * @param Request $request
     * @param Content $content
     * @return \Illuminate\View\View|Content
     */
    public function index(Request $request, Content $content)
    {
        // standalone 或 pjax：直接返回 Vue 视图（vue-app 布局处理）
        if ($request->get('standalone') || $request->pjax()) {
            return view('featuredbadmin::vue.dashboard');
        }

        // 普通请求：用 Content 包装后台布局，body 返回 Vue 视图
        // Vue 视图会渲染 iframe 容器
        return $content
            ->title('数据库管理员工具')
            ->description('系统概览')
            ->body(view('featuredbadmin::vue.dashboard'));
    }

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
