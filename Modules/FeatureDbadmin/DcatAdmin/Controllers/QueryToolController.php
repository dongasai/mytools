<?php

namespace Modules\FeatureDbadmin\DcatAdmin\Controllers;

use Dcat\Admin\Layout\Content;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\FeatureDbadmin\Models\Connection;
use Modules\FeatureDbadmin\Models\QueryHistory;
use Modules\FeatureDbadmin\Models\SavedQuery;
use Modules\FeatureDbadmin\Services\QueryService;

/**
 * SQL查询工具控制器
 *
 * 提供SQL执行、历史记录、保存查询功能
 * HTML页面和JSON API混合控制器
 */
class QueryToolController extends Controller
{
    /**
     * SQL查询工具页
     *
     * 加载Vue组件，提供SQL编辑器
     *
     * @param Content $content
     * @return Content
     */

    /**
     * 执行SQL查询
     *
     * 验证SQL安全性后执行
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function execute(Request $request)
    {
        $validated = $request->validate([
            'connection_id' => 'required|integer|min:1',
            'sql' => 'required|string|min:1',
        ]);

        $connectionId = (int) $validated['connection_id'];
        $sql = $validated['sql'];

        // 验证SQL安全性
        if (!QueryService::validateQuery($sql)) {
            return response()->json([
                'success' => false,
                'message' => 'SQL包含危险关键字，执行被拒绝',
                'data' => [],
                'row_count' => 0,
                'execution_time' => 0,
            ]);
        }

        $result = QueryService::executeQuery($connectionId, $sql);

        return response()->json([
            'success' => $result->success,
            'message' => $result->message,
            'data' => $result->data,
            'row_count' => $result->rowCount,
            'execution_time' => $result->executionTime,
            'columns' => $result->columns,
        ]);
    }

    /**
     * 获取查询历史
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function history(Request $request)
    {
        $validated = $request->validate([
            'connection_id' => 'nullable|integer|min:1',
            'user_id' => 'nullable|integer|min:1',
            'limit' => 'integer|min:1|max:100',
        ]);

        $connectionId = $validated['connection_id'] ?? null;
        $userId = $validated['user_id'] ?? null;
        $limit = (int) ($validated['limit'] ?? 50);

        $query = QueryHistory::query();

        if ($connectionId !== null) {
            $connection = Connection::find($connectionId);
            if ($connection) {
                $query->where('connection_name', $connection->name);
            }
        }

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        $history = $query->orderBy('executed_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => $history,
        ]);
    }

    /**
     * 获取保存的查询列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saved(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|integer|min:1',
            'is_public' => 'nullable|boolean',
        ]);

        $userId = $validated['user_id'] ?? null;
        $isPublic = $validated['is_public'] ?? null;

        $query = SavedQuery::query();

        if ($userId !== null) {
            $query->where(function ($q) use ($userId, $isPublic) {
                $q->where('user_id', $userId);
                if ($isPublic !== false) {
                    $q->orWhere('is_public', true);
                }
            });
        } elseif ($isPublic !== null) {
            $query->where('is_public', $isPublic);
        }

        $savedQueries = $query->orderBy('use_count', 'desc')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $savedQueries,
        ]);
    }

    /**
     * 保存查询
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'connection_id' => 'required|integer|min:1',
            'sql' => 'required|string',
            'tags' => 'nullable|array',
            'is_public' => 'boolean',
        ]);

        $connection = Connection::find($validated['connection_id']);
        $connectionName = $connection ? $connection->name : 'unknown';

        $savedQuery = SavedQuery::create([
            'user_id' => $request->user()?->id ?? 0,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'connection_name' => $connectionName,
            'sql_query' => $validated['sql'],
            'tags' => $validated['tags'] ?? [],
            'is_public' => $validated['is_public'] ?? false,
            'use_count' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => '查询保存成功',
            'data' => $savedQuery,
        ]);
    }
}
