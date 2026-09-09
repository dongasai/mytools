<?php

namespace Modules\FeatureDbadmin\DcatAdmin\Controllers;

use Dcat\Admin\Layout\Content;
use Illuminate\Http\Request;
use Modules\DcatAdmin\DcatAdmin\AdminController;
use Modules\FeatureDbadmin\Models\Connection;
use Modules\FeatureDbadmin\Services\DataBrowserService;
use Modules\FeatureDbadmin\Services\ExportService;

/**
 * 数据浏览控制器
 *
 * 提供表数据浏览、编辑、导出功能
 * HTML页面和JSON API混合控制器
 */
class DataBrowserController extends AdminController
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

        // 获取主键字段名，并添加到每行数据中
        $connection = \Modules\FeatureDbadmin\Models\Connection::find($connectionId);
        if ($connection) {
            $connection->registerDynamicConnection();
            $connectionName = $connection->getDynamicConnectionName();
            $primaryKey = \Modules\FeatureDbadmin\Services\DataBrowserService::getPrimaryKeyNamePublic($connectionName, $tableName);

            // 为每行数据添加 _pk 字段
            foreach ($result['data'] as &$row) {
                if (is_object($row)) {
                    $row->_pk = $row->$primaryKey ?? null;
                } elseif (is_array($row)) {
                    $row['_pk'] = $row[$primaryKey] ?? null;
                }
            }
            unset($row); // 解除引用

            $result['_pk_field'] = $primaryKey;
        }

        return $this->success_json($result);
    }

    /**
     * 获取单行数据
     *
     * @param Request $request
     * @param string $tableName
     * @param string|int $id 主键值（支持字符串主键）
     * @return \Illuminate\Http\JsonResponse
     */
    public function row(Request $request, string $tableName, string|int $id)
    {
        $validated = $request->validate([
            'connection_id' => 'required|integer|min:1',
        ]);

        $connectionId = (int) $validated['connection_id'];
        $data = DataBrowserService::getRow($connectionId, $tableName, $id);

        return $this->success_json(['data' => $data]);
    }

    /**
     * 新增数据
     *
     * @param Request $request
     * @param string $tableName
     * @return \Illuminate\Http\JsonResponse
     */
    public function insert(Request $request, string $tableName)
    {
        $validated = $request->validate([
            'connection_id' => 'required|integer|min:1',
            'data' => 'required|array',
        ]);

        $connectionId = (int) $validated['connection_id'];
        $data = $validated['data'];

        try {
            $result = DataBrowserService::insertRow($connectionId, $tableName, $data);

            // insertRow 返回值：
            // - 0: 失败
            // - 1: 成功（无自增主键）
            // - >1: 成功（自增主键 ID）
            if ($result > 0) {
                $responseData = [];

                // 如果返回的是真正的 ID（大于 1），才返回 id 字段
                if ($result > 1) {
                    $responseData['id'] = $result;
                }

                return $this->success_json($responseData, '数据新增成功');
            }

            return $this->error_json('数据新增失败');
        } catch (\Illuminate\Database\QueryException $e) {
            // 解析数据库错误，返回友好提示
            $errorMessage = $this->parseDatabaseError($e);

            return $this->error_json($errorMessage, 422);
        }
    }

    /**
     * 更新数据
     *
     * @param Request $request
     * @param string $tableName
     * @param string|int $id 主键值（支持字符串主键）
     * @return \Illuminate\Http\JsonResponse
     */
    public function modify(Request $request, string $tableName, string|int $id)
    {
        $validated = $request->validate([
            'connection_id' => 'required|integer|min:1',
            'data' => 'required|array',
        ]);

        $connectionId = (int) $validated['connection_id'];
        $data = $validated['data'];

        try {
            $success = DataBrowserService::updateRow($connectionId, $tableName, $id, $data);

            if ($success) {
                return $this->success_json(null, '数据更新成功');
            }

            return $this->error_json('数据更新失败');
        } catch (\Illuminate\Database\QueryException $e) {
            // 解析数据库错误，返回友好提示
            $errorMessage = $this->parseDatabaseError($e);

            return $this->error_json($errorMessage, 422);
        }
    }

    /**
     * 删除数据
     *
     * @param Request $request
     * @param string $tableName
     * @param string|int $id 主键值（支持字符串主键）
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request, string $tableName, string|int $id)
    {
        $validated = $request->validate([
            'connection_id' => 'required|integer|min:1',
        ]);

        $connectionId = (int) $validated['connection_id'];
        $success = DataBrowserService::deleteRow($connectionId, $tableName, $id);

        if ($success) {
            return $this->success_json(null, '数据删除成功');
        }

        return $this->error_json('数据删除失败');
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

        return $this->success_json([
            'data' => $content,
            'format' => $format,
        ]);
    }

    /**
     * 解析数据库错误，返回友好的错误提示
     *
     * @param \Illuminate\Database\QueryException $e
     * @return string
     */
    protected function parseDatabaseError(\Illuminate\Database\QueryException $e): string
    {
        $sqlState = $e->getCode();
        $message = $e->getMessage();

        // 提取字段名和值
        $fieldName = null;
        $fieldValue = null;

        // PostgreSQL 错误格式："birth_date" 字段
        if (preg_match('/"(\w+)"/', $message, $matches)) {
            $fieldName = $matches[1];
        }

        // PostgreSQL 错误格式：parameter $10 = '...'
        if (preg_match('/parameter \$\d+ = \'([^\']+)\'/', $message, $matches)) {
            $fieldValue = $matches[1];
        }

        // 根据不同的错误类型返回友好提示
        // PostgreSQL 错误代码
        if (str_contains($message, 'Invalid datetime format') || str_contains($message, 'invalid input syntax for type date')) {
            if ($fieldValue) {
                $fieldHint = $fieldName ? "字段 '{$fieldName}' 的值 " : '';
                return "日期格式错误：{$fieldHint}'{$fieldValue}' 不是有效的日期格式。请使用 YYYY-MM-DD 格式（如：2026-05-15）";
            }
            return '日期格式错误：请使用 YYYY-MM-DD 格式（如：2026-05-15）';
        }

        if (str_contains($message, 'invalid input syntax for type timestamp')) {
            if ($fieldValue) {
                $fieldHint = $fieldName ? "字段 '{$fieldName}' 的值 " : '';
                return "时间戳格式错误：{$fieldHint}'{$fieldValue}' 不是有效的时间戳格式。请使用 YYYY-MM-DD HH:MM:SS 格式（如：2026-05-15 10:30:00）";
            }
            return '时间戳格式错误：请使用 YYYY-MM-DD HH:MM:SS 格式（如：2026-05-15 10:30:00）';
        }

        if (str_contains($message, 'invalid input syntax for type integer')) {
            if ($fieldValue) {
                $fieldHint = $fieldName ? "字段 '{$fieldName}' 的值 " : '';
                return "整数格式错误：{$fieldHint}'{$fieldValue}' 不是有效的整数。请输入数字（如：123）";
            }
            return '整数格式错误：请输入有效的数字';
        }

        if (str_contains($message, 'invalid input syntax for type numeric') || str_contains($message, 'invalid input syntax for type decimal')) {
            if ($fieldValue) {
                $fieldHint = $fieldName ? "字段 '{$fieldName}' 的值 " : '';
                return "数字格式错误：{$fieldHint}'{$fieldValue}' 不是有效的数字。请输入数字（如：88.50）";
            }
            return '数字格式错误：请输入有效的数字';
        }

        // MySQL 错误
        if (str_contains($message, 'Incorrect date value')) {
            return '日期格式错误：请使用 YYYY-MM-DD 格式（如：2026-05-15）';
        }

        if (str_contains($message, 'Incorrect datetime value')) {
            return '日期时间格式错误：请使用 YYYY-MM-DD HH:MM:SS 格式（如：2026-05-15 10:30:00）';
        }

        if (str_contains($message, 'Incorrect integer value')) {
            return '整数格式错误：请输入有效的整数';
        }

        if (str_contains($message, 'Incorrect decimal value')) {
            return '数字格式错误：请输入有效的数字';
        }

        // 唯一键冲突
        if (str_contains($message, 'Duplicate entry') || str_contains($message, 'unique constraint')) {
            return '数据重复：该值已存在，请使用其他值';
        }

        // 外键约束错误
        if (str_contains($message, 'foreign key constraint')) {
            return '外键约束错误：关联的数据不存在';
        }

        // 字段长度超限
        if (str_contains($message, 'Data too long') || str_contains($message, 'value too long')) {
            return '数据长度超限：输入的数据超过了字段最大长度限制';
        }

        // 空值错误
        if (str_contains($message, 'cannot be null') || str_contains($message, 'null value')) {
            if ($fieldName) {
                return "必填字段错误：字段 '{$fieldName}' 不能为空";
            }
            return '必填字段错误：某些必填字段未填写';
        }

        // 默认：返回简化后的错误信息
        // 移除 SQL 语句和敏感信息
        $cleanMessage = preg_replace('/\s+SQL:\s+\[.*/', '', $message);
        $cleanMessage = preg_replace('/\(Connection:.*?\)/', '', $cleanMessage);

        return '数据库错误：' . trim($cleanMessage);
    }
}
