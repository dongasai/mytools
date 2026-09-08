<?php

namespace Modules\FeatureDbadmin\DcatAdmin\Controllers;

use Dcat\Admin\Layout\Content;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\FeatureDbadmin\Models\Connection;
use Modules\FeatureDbadmin\Services\TableService;
use Modules\FeatureDbadmin\Services\ExportService;

/**
 * 表管理控制器
 *
 * 提供表结构查看、导出功能
 * HTML页面和JSON API混合控制器
 */
class TableController extends Controller
{
    /**
     * 表列表页
     *
     * 加载Vue组件，显示数据库表列表
     *
     * @param Content $content
     * @return Content
     */

    /**
     * 获取表列表
     *
     * 返回指定连接的所有表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        $validated = $request->validate([
            'connection_id' => 'required|integer|min:1',
        ]);

        $connectionId = (int) $validated['connection_id'];
        $tables = TableService::getAllTables($connectionId);

        return response()->json([
            'data' => $tables,
            'total' => count($tables),
        ]);
    }

    /**
     * 获取表结构详情
     *
     * 返回指定表的完整结构信息
     *
     * @param Request $request
     * @param string $tableName
     * @return \Illuminate\Http\JsonResponse
     */
    public function structure(Request $request, string $tableName)
    {
        $validated = $request->validate([
            'connection_id' => 'required|integer|min:1',
        ]);

        $connectionId = (int) $validated['connection_id'];
        $structure = TableService::getTableStructure($connectionId, $tableName);

        return response()->json([
            'data' => $structure,
        ]);
    }

    /**
     * 导出表结构
     *
     * 导出为SQL或Markdown格式
     *
     * @param Request $request
     * @param string $tableName
     * @return \Illuminate\Http\JsonResponse
     */
    public function export(Request $request, string $tableName)
    {
        $validated = $request->validate([
            'connection_id' => 'required|integer|min:1',
            'format' => 'required|string|in:sql,markdown',
        ]);

        $connectionId = (int) $validated['connection_id'];
        $format = $validated['format'];

        if ($format === 'sql') {
            $content = ExportService::exportStructureToSql($connectionId, $tableName);
        } else {
            $content = ExportService::exportStructureToMarkdown($connectionId, $tableName);
        }

        return response()->json([
            'data' => $content,
            'format' => $format,
        ]);
    }
}
