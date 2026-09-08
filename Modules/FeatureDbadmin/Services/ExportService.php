<?php

namespace Modules\FeatureDbadmin\Services;

use Illuminate\Support\Facades\DB;
use Modules\FeatureDbadmin\Models\Connection;

/**
 * 导出服务
 *
 * 导出表结构、导出数据
 * 所有方法均为静态方法
 */
class ExportService
{
    /**
     * 导出表结构为 SQL
     *
     * 使用 SHOW CREATE TABLE 获取建表语句
     *
     * @param int $connectionId 连接ID
     * @param string $tableName 表名
     * @return string SQL 建表语句
     */
    public static function exportStructureToSql(int $connectionId, string $tableName): string
    {
        // 获取连接
        $connection = Connection::find($connectionId);
        if (!$connection) {
            return '';
        }

        // 注册动态连接
        $connection->registerDynamicConnection();
        $connectionName = $connection->getDynamicConnectionName();

        $driver = $connection->driver;

        if ($driver === 'mysql') {
            $result = DB::connection($connectionName)->selectOne("SHOW CREATE TABLE `{$tableName}`");
            if ($result) {
                return $result->{'Create Table'} ?? '';
            }
        } elseif ($driver === 'pgsql') {
            // PostgreSQL 使用 information_schema 获取表结构
            $columns = DB::connection($connectionName)
                ->table('information_schema.columns')
                ->select('column_name', 'data_type', 'is_nullable', 'column_default')
                ->where('table_name', $tableName)
                ->where('table_schema', 'public')
                ->orderBy('ordinal_position')
                ->get();

            if ($columns->isEmpty()) {
                return '';
            }

            $columnDefs = [];
            foreach ($columns as $col) {
                $def = "    \"{$col->column_name}\" {$col->data_type}";
                if ($col->is_nullable === 'NO') {
                    $def .= ' NOT NULL';
                }
                $columnDefs[] = $def;
            }

            return "CREATE TABLE \"{$tableName}\" (\n" . implode(",\n", $columnDefs) . "\n);";
        } elseif ($driver === 'sqlite') {
            $result = DB::connection($connectionName)->selectOne("SELECT sql FROM sqlite_master WHERE type='table' AND name=?", [$tableName]);
            return $result->sql ?? '';
        }

        return '';
    }

    /**
     * 导出数据为 CSV
     *
     * 第一行为列名，后续为数据行
     * 处理特殊字符转义
     *
     * @param int $connectionId 连接ID
     * @param string $tableName 表名
     * @param array<string, mixed> $filters 筛选条件
     * @return string CSV 格式字符串
     */
    public static function exportDataToCsv(int $connectionId, string $tableName, array $filters = []): string
    {
        // 获取连接
        $connection = Connection::find($connectionId);
        if (!$connection) {
            return '';
        }

        // 注册动态连接
        $connection->registerDynamicConnection();
        $connectionName = $connection->getDynamicConnectionName();

        // 构建查询
        $query = DB::connection($connectionName)->table($tableName);

        // 应用筛选条件
        foreach ($filters as $column => $value) {
            if (is_array($value)) {
                $query->whereIn($column, $value);
            } else {
                $query->where($column, $value);
            }
        }

        // 获取数据
        $data = $query->get();

        if ($data->isEmpty()) {
            return '';
        }

        // 获取列名
        $columns = array_keys((array) $data->first());

        // 创建临时输出缓冲区
        $output = fopen('php://temp', 'r+');

        // 写入表头
        fputcsv($output, $columns);

        // 写入数据行
        foreach ($data as $row) {
            $rowArray = (array) $row;
            fputcsv($output, $rowArray);
        }

        // 获取 CSV 内容
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv ?: '';
    }

    /**
     * 导出数据为 JSON
     *
     * 数组对象格式
     * 处理特殊字符转义
     *
     * @param int $connectionId 连接ID
     * @param string $tableName 表名
     * @param array<string, mixed> $filters 筛选条件
     * @return string JSON 格式字符串
     */
    public static function exportDataToJson(int $connectionId, string $tableName, array $filters = []): string
    {
        // 获取连接
        $connection = Connection::find($connectionId);
        if (!$connection) {
            return '[]';
        }

        // 注册动态连接
        $connection->registerDynamicConnection();
        $connectionName = $connection->getDynamicConnectionName();

        // 构建查询
        $query = DB::connection($connectionName)->table($tableName);

        // 应用筛选条件
        foreach ($filters as $column => $value) {
            if (is_array($value)) {
                $query->whereIn($column, $value);
            } else {
                $query->where($column, $value);
            }
        }

        // 获取数据
        $data = $query->get();

        // 转换为数组
        $arrayData = [];
        foreach ($data as $row) {
            $arrayData[] = (array) $row;
        }

        return json_encode($arrayData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?: '[]';
    }

    /**
     * 导出数据为 SQL INSERT 语句
     *
     * 生成 INSERT INTO ... VALUES ... 语句
     *
     * @param int $connectionId 连接ID
     * @param string $tableName 表名
     * @param array<string, mixed> $filters 筛选条件
     * @return string SQL INSERT 语句
     */
    public static function exportDataToSql(int $connectionId, string $tableName, array $filters = []): string
    {
        // 获取连接
        $connection = Connection::find($connectionId);
        if (!$connection) {
            return '';
        }

        // 注册动态连接
        $connection->registerDynamicConnection();
        $connectionName = $connection->getDynamicConnectionName();

        $driver = $connection->driver;

        // 构建查询
        $query = DB::connection($connectionName)->table($tableName);

        // 应用筛选条件
        foreach ($filters as $column => $value) {
            if (is_array($value)) {
                $query->whereIn($column, $value);
            } else {
                $query->where($column, $value);
            }
        }

        // 获取数据
        $data = $query->get();

        if ($data->isEmpty()) {
            return '';
        }

        $sqlLines = [];

        foreach ($data as $row) {
            $rowArray = (array) $row;
            $columns = array_keys($rowArray);
            $values = array_values($rowArray);

            // 转义值
            $escapedValues = array_map(function ($value) use ($driver) {
                if ($value === null) {
                    return 'NULL';
                }
                if (is_bool($value)) {
                    return $value ? '1' : '0';
                }
                if (is_numeric($value)) {
                    return $value;
                }
                // 字符串转义
                return "'" . addslashes((string) $value) . "'";
            }, $values);

            $columnStr = implode(', ', $columns);
            $valueStr = implode(', ', $escapedValues);

            $sqlLines[] = "INSERT INTO `{$tableName}` ({$columnStr}) VALUES ({$valueStr});";
        }

        return implode("\n", $sqlLines);
    }

    /**
     * 导出表结构为 Markdown
     *
     * 生成字段说明文档
     *
     * @param int $connectionId 连接ID
     * @param string $tableName 表名
     * @return string Markdown 格式文档
     */
    public static function exportStructureToMarkdown(int $connectionId, string $tableName): string
    {
        // 获取表结构信息
        $structure = SchemaService::getTableStructure($connectionId, $tableName);

        if (empty($structure)) {
            return '';
        }

        $markdown = "# 表结构: {$tableName}\n\n";
        $markdown .= "## 字段列表\n\n";
        $markdown .= "| 字段名 | 类型 | 可空 | 默认值 | 注释 |\n";
        $markdown .= "|--------|------|------|--------|------|\n";

        foreach ($structure['columns'] as $column) {
            $nullable = $column['nullable'] ? '是' : '否';
            $default = $column['default'] ?? 'NULL';
            $markdown .= "| {$column['name']} | {$column['type']} | {$nullable} | {$default} | {$column['comment']} |\n";
        }

        if (!empty($structure['indexes'])) {
            $markdown .= "\n## 索引\n\n";
            $markdown .= "| 索引名 | 类型 | 字段 |\n";
            $markdown .= "|--------|------|------|\n";

            foreach ($structure['indexes'] as $index) {
                $columns = implode(', ', $index['columns']);
                $markdown .= "| {$index['name']} | {$index['type']} | {$columns} |\n";
            }
        }

        return $markdown;
    }
}
