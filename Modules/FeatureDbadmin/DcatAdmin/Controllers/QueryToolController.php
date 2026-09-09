<?php

namespace Modules\FeatureDbadmin\DcatAdmin\Controllers;

use Dcat\Admin\Layout\Content;
use Illuminate\Http\Request;
use Modules\DcatAdmin\DcatAdmin\AdminController;
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
class QueryToolController extends AdminController
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
        try {
            $validated = $request->validate([
                'connection_id' => 'required|integer|min:1',
                'sql' => 'required|string|min:1',
            ]);

            $connectionId = (int) $validated['connection_id'];
            $sql = $validated['sql'];

            // 执行查询（QueryService 内部会验证 SQL 安全性并捕获异常）
            $result = QueryService::executeQuery($connectionId, $sql);

            return response()->json([
                'success' => $result->success,
                'message' => $result->message,
                'sql' => $sql,
                'data' => $result->data,
                'row_count' => $result->rowCount,
                'execution_time' => $result->executionTime,
                'columns' => $result->columns,
            ]);
        } catch (\Exception $e) {
            // 捕获未预期的异常
            return response()->json([
                'success' => false,
                'message' => '服务器错误: ' . $e->getMessage(),
                'sql' => $request->input('sql', ''),
                'data' => [],
                'row_count' => 0,
                'execution_time' => 0,
                'columns' => [],
            ], 500);
        }
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
            'connection_id' => 'nullable|integer|min:1',
            'database' => 'nullable|string',
            'schema' => 'nullable|string',
            'user_id' => 'nullable|integer|min:1',
            'is_public' => 'nullable|boolean',
        ]);

        $connectionId = $validated['connection_id'] ?? null;
        $database = $validated['database'] ?? null;
        $schema = $validated['schema'] ?? null;
        $userId = $validated['user_id'] ?? null;
        $isPublic = $validated['is_public'] ?? null;

        $query = SavedQuery::query();

        // 按连接ID过滤
        if ($connectionId !== null) {
            $query->where('connection_id', $connectionId);
        }

        // 按数据库过滤
        if ($database !== null) {
            $query->where('database', $database);
        }

        // 按模式过滤
        if ($schema !== null) {
            $query->where('schema', $schema);
        }

        // 按用户过滤
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
            'success' => true,
            'data' => $savedQueries,
        ]);
    }

    /**
     * 获取单个保存的查询
     *
     * @param int $id 查询ID
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail(int $id, Request $request)
    {
        $validated = $request->validate([
            'connection_id' => 'nullable|integer|min:1',
        ]);

        $savedQuery = SavedQuery::find($id);

        if (!$savedQuery) {
            return response()->json([
                'success' => false,
                'message' => '查询不存在',
            ], 404);
        }

        // 增加使用次数
        $savedQuery->incrementUsage();

        return response()->json([
            'success' => true,
            'data' => $savedQuery,
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
            'database' => 'nullable|string',
            'schema' => 'nullable|string',
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
            'connection_id' => $validated['connection_id'],
            'connection_name' => $connectionName,
            'database' => $validated['database'] ?? null,
            'schema' => $validated['schema'] ?? null,
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

    /**
     * 更新保存的查询
     *
     * @param int $id 查询ID
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(int $id, Request $request)
    {
        $savedQuery = SavedQuery::find($id);

        if (!$savedQuery) {
            return response()->json([
                'success' => false,
                'message' => '查询不存在',
            ], 404);
        }

        $validated = $request->validate([
            'connection_id' => 'nullable|integer|min:1',
            'database' => 'nullable|string',
            'schema' => 'nullable|string',
            'sql' => 'nullable|string',
            'description' => 'nullable|string|max:1000',
            'tags' => 'nullable|array',
            'is_public' => 'boolean',
        ]);

        // 更新字段
        if (isset($validated['connection_id'])) {
            $connection = Connection::find($validated['connection_id']);
            $savedQuery->connection_id = $validated['connection_id'];
            $savedQuery->connection_name = $connection ? $connection->name : 'unknown';
        }

        if (isset($validated['database'])) {
            $savedQuery->database = $validated['database'];
        }

        if (isset($validated['schema'])) {
            $savedQuery->schema = $validated['schema'];
        }

        if (isset($validated['sql'])) {
            $savedQuery->sql_query = $validated['sql'];
        }

        if (isset($validated['description'])) {
            $savedQuery->description = $validated['description'];
        }

        if (isset($validated['tags'])) {
            $savedQuery->tags = $validated['tags'];
        }

        if (isset($validated['is_public'])) {
            $savedQuery->is_public = $validated['is_public'];
        }

        $savedQuery->save();

        return response()->json([
            'success' => true,
            'message' => '查询更新成功',
            'data' => $savedQuery,
        ]);
    }
}
