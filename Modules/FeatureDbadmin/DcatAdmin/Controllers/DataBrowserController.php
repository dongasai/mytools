<?php

namespace Modules\FeatureDbadmin\DcatAdmin\Controllers;

use Dcat\Admin\Layout\Content;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\FeatureDbadmin\Models\Connection;
use Modules\FeatureDbadmin\Services\DataBrowserService;
use Modules\FeatureDbadmin\Services\ExportService;

/**
 * 数据浏览控制器
 *
 * 提供表数据浏览、编辑、导出功能
 * HTML页面和JSON API混合控制器
 */
class DataBrowserController extends Controller
{
    /**
     * 数据浏览页
     *
     * 加载Vue组件，显示表数据
     *
     * @param Content $content
     * @param string $tableName
     * @return Content
     */

    /**
     * 获取数据列表
     *
     * 分页返回表数据
     *
     * @param Request $request
     * @param string $tableName
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request, string $tableName)
    {
        $validated = $request->validate([
            'connection_id' => 'required|integer|min:1',
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:500',
            'order_by' => 'nullable|array',
            'filters' => 'nullable|array',
        ]);

        $connectionId = (int) $validated['connection_id'];
        $page = (int) ($validated['page'] ?? 1);
        $perPage = (int) ($validated['per_page'] ?? 20);
        $orderBy = $validated['order_by'] ?? [];
        $filters = $validated['filters'] ?? [];

        $result = DataBrowserService::getTableData(
            $connectionId,
            $tableName,
            $page,
            $perPage,
            $orderBy,
            $filters
        );

        return response()->json($result);
    }

    /**
     * 获取单行数据
     *
     * @param Request $request
     * @param string $tableName
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function row(Request $request, string $tableName, int $id)
    {
        $validated = $request->validate([
            'connection_id' => 'required|integer|min:1',
        ]);

        $connectionId = (int) $validated['connection_id'];
        $data = DataBrowserService::getRow($connectionId, $tableName, $id);

        return response()->json([
            'data' => $data,
        ]);
    }

    /**
     * 新增数据
     *
     * @param Request $request
     * @param string $tableName
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request, string $tableName)
    {
        $validated = $request->validate([
            'connection_id' => 'required|integer|min:1',
            'data' => 'required|array',
        ]);

        $connectionId = (int) $validated['connection_id'];
        $data = $validated['data'];

        $id = DataBrowserService::insertRow($connectionId, $tableName, $data);

        if ($id > 0) {
            return response()->json([
                'success' => true,
                'message' => '数据新增成功',
                'id' => $id,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => '数据新增失败',
            'id' => 0,
        ]);
    }

    /**
     * 更新数据
     *
     * @param Request $request
     * @param string $tableName
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $tableName, int $id)
    {
        $validated = $request->validate([
            'connection_id' => 'required|integer|min:1',
            'data' => 'required|array',
        ]);

        $connectionId = (int) $validated['connection_id'];
        $data = $validated['data'];

        $success = DataBrowserService::updateRow($connectionId, $tableName, $id, $data);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => '数据更新成功',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => '数据更新失败',
        ]);
    }

    /**
     * 删除数据
     *
     * @param Request $request
     * @param string $tableName
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request, string $tableName, int $id)
    {
        $validated = $request->validate([
            'connection_id' => 'required|integer|min:1',
        ]);

        $connectionId = (int) $validated['connection_id'];
        $success = DataBrowserService::deleteRow($connectionId, $tableName, $id);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => '数据删除成功',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => '数据删除失败',
        ]);
    }

    /**
     * 导出数据
     *
     * 导出为CSV、JSON或SQL格式
     *
     * @param Request $request
     * @param string $tableName
     * @return \Illuminate\Http\JsonResponse
     */
    public function export(Request $request, string $tableName)
    {
        $validated = $request->validate([
            'connection_id' => 'required|integer|min:1',
            'format' => 'required|string|in:csv,json,sql',
            'filters' => 'nullable|array',
        ]);

        $connectionId = (int) $validated['connection_id'];
        $format = $validated['format'];
        $filters = $validated['filters'] ?? [];

        switch ($format) {
            case 'csv':
                $content = ExportService::exportDataToCsv($connectionId, $tableName, $filters);
                break;
            case 'json':
                $content = ExportService::exportDataToJson($connectionId, $tableName, $filters);
                break;
            case 'sql':
                $content = ExportService::exportDataToSql($connectionId, $tableName, $filters);
                break;
            default:
                $content = '';
        }

        return response()->json([
            'data' => $content,
            'format' => $format,
        ]);
    }
}
