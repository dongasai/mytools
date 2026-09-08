<?php

namespace Modules\FeatureDbadmin\Services;

use Illuminate\Support\Facades\DB;
use Modules\FeatureDbadmin\Models\Connection;

/**
 * 数据浏览服务
 *
 * 浏览表数据、更新数据、插入数据、删除数据
 * 所有方法均为静态方法
 */
class DataBrowserService
{
    /**
     * 获取表数据（分页）
     *
     * 使用 DB::table($tableName)
     * 支持排序和筛选
     *
     * @param int $connectionId 连接ID
     * @param string $tableName 表名
     * @param int $page 当前页码
     * @param int $perPage 每页条数
     * @param array<string, string> $orderBy 排序条件 [column => direction]
     * @param array<string, mixed> $filters 筛选条件 [column => value]
     * @return array{data: array, total: int, page: int, per_page: int}
     */
    public static function getTableData(
        int $connectionId,
        string $tableName,
        int $page = 1,
        int $perPage = 20,
        array $orderBy = [],
        array $filters = []
    ): array {
        // 获取连接
        $connection = Connection::find($connectionId);
        if (!$connection) {
            return [
                'data' => [],
                'total' => 0,
                'page' => $page,
                'per_page' => $perPage,
            ];
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

        // 克隆查询用于计数
        $total = $query->count();

        // 应用排序
        foreach ($orderBy as $column => $direction) {
            $query->orderBy($column, strtolower($direction) === 'desc' ? 'desc' : 'asc');
        }

        // 应用分页
        $offset = ($page - 1) * $perPage;
        $data = $query->offset($offset)->limit($perPage)->get()->toArray();

        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
        ];
    }

    /**
     * 获取单行数据
     *
     * 使用 DB::table($tableName)->where('id', $id)->first()
     *
     * @param int $connectionId 连接ID
     * @param string $tableName 表名
     * @param int $id 数据ID
     * @return object|null 数据对象或 null
     */
    public static function getRow(int $connectionId, string $tableName, int $id): ?object
    {
        // 获取连接
        $connection = Connection::find($connectionId);
        if (!$connection) {
            return null;
        }

        // 注册动态连接
        $connection->registerDynamicConnection();
        $connectionName = $connection->getDynamicConnectionName();

        return DB::connection($connectionName)->table($tableName)->where('id', $id)->first();
    }

    /**
     * 插入数据
     *
     * 使用 DB::table($tableName)->insertGetId($data)
     *
     * @param int $connectionId 连接ID
     * @param string $tableName 表名
     * @param array<string, mixed> $data 插入数据
     * @return int 插入的 ID
     */
    public static function insertRow(int $connectionId, string $tableName, array $data): int
    {
        // 获取连接
        $connection = Connection::find($connectionId);
        if (!$connection) {
            return 0;
        }

        // 注册动态连接
        $connection->registerDynamicConnection();
        $connectionName = $connection->getDynamicConnectionName();

        return DB::connection($connectionName)->table($tableName)->insertGetId($data);
    }

    /**
     * 更新数据
     *
     * 使用 DB::table($tableName)->where('id', $id)->update($data)
     *
     * @param int $connectionId 连接ID
     * @param string $tableName 表名
     * @param int $id 数据ID
     * @param array<string, mixed> $data 更新数据
     * @return bool 是否成功
     */
    public static function updateRow(int $connectionId, string $tableName, int $id, array $data): bool
    {
        // 获取连接
        $connection = Connection::find($connectionId);
        if (!$connection) {
            return false;
        }

        // 注册动态连接
        $connection->registerDynamicConnection();
        $connectionName = $connection->getDynamicConnectionName();

        $affected = DB::connection($connectionName)->table($tableName)->where('id', $id)->update($data);

        return $affected > 0;
    }

    /**
     * 删除数据
     *
     * 使用 DB::table($tableName)->where('id', $id)->delete()
     *
     * @param int $connectionId 连接ID
     * @param string $tableName 表名
     * @param int $id 数据ID
     * @return bool 是否成功
     */
    public static function deleteRow(int $connectionId, string $tableName, int $id): bool
    {
        // 获取连接
        $connection = Connection::find($connectionId);
        if (!$connection) {
            return false;
        }

        // 注册动态连接
        $connection->registerDynamicConnection();
        $connectionName = $connection->getDynamicConnectionName();

        $deleted = DB::connection($connectionName)->table($tableName)->where('id', $id)->delete();

        return $deleted > 0;
    }
}
