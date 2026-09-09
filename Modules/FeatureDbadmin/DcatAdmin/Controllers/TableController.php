<?php

namespace Modules\FeatureDbadmin\DcatAdmin\Controllers;

use Dcat\Admin\Layout\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\DcatAdmin\DcatAdmin\AdminController;
use Modules\FeatureDbadmin\Models\Connection;
use Modules\FeatureDbadmin\Services\TableService;
use Modules\FeatureDbadmin\Services\ExportService;

/**
 * 表管理控制器
 *
 * 提供表结构查看、导出功能
 * HTML页面和JSON API混合控制器
 */
class TableController extends AdminController
{
    /**
     * 获取数据库列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function databases(Request $request)
    {
        $validated = $request->validate([
            'connection_id' => 'required|integer|min:1',
        ]);

        $connectionId = (int) $validated['connection_id'];

        try {
            $driver = \Modules\FeatureDbadmin\Services\Drivers\DriverFactory::createFromId($connectionId);
            $databases = $driver->getDatabases();

            return $this->success_json($databases);
        } catch (\Exception $e) {
            return $this->error_json($e->getMessage());
        }
    }

    /**
     * 获取模式列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function schemas(Request $request)
    {
        $validated = $request->validate([
            'connection_id' => 'required|integer|min:1',
            'database' => 'required|string',
        ]);

        $connectionId = (int) $validated['connection_id'];
        $database = $validated['database'];

        try {
            $driver = \Modules\FeatureDbadmin\Services\Drivers\DriverFactory::createFromId($connectionId);
            $schemas = $driver->getSchemas($database);

            return $this->success_json($schemas);
        } catch (\Exception $e) {
            return $this->error_json($e->getMessage());
        }
    }

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
            'database' => 'nullable|string',
            'schema' => 'nullable|string',
        ]);

        $connectionId = (int) $validated['connection_id'];
        $database = $validated['database'] ?? '';
        $schema = $validated['schema'] ?? '';

        try {
            $driver = \Modules\FeatureDbadmin\Services\Drivers\DriverFactory::createFromId($connectionId);
            $tables = $driver->getAllTables($database, $schema);

            return $this->success_json([
                'data' => $tables,
                'total' => count($tables),
            ]);
        } catch (\Exception $e) {
            return $this->error_json($e->getMessage());
        }
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

        return $this->success_json($structure);
    }

    /**
     * 创建测试表
     *
     * 创建包含各种字段类型的测试表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createTestTable(Request $request)
    {
        $validated = $request->validate([
            'connection_id' => 'required|integer|min:1',
        ]);

        $connectionId = (int) $validated['connection_id'];

        try {
            $connection = Connection::find($connectionId);
            if (!$connection) {
                return $this->error_json('连接不存在');
            }

            // 使用驱动创建测试表
            $driver = \Modules\FeatureDbadmin\Services\Drivers\DriverFactory::create($connection);
            $tableName = 'featuredbadmin_test_table';
            $driver->createTestTable($tableName);

            return $this->success_json([
                'table_name' => $tableName,
            ], '测试表创建成功');
        } catch (\Exception $e) {
            return $this->error_json('创建失败: ' . $e->getMessage());
        }
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

        return $this->success_json([
            'data' => $content,
            'format' => $format,
        ]);
    }
}