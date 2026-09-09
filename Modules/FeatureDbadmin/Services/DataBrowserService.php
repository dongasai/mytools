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
     * 支持任意主键字段（id、key、uuid等）
     *
     * @param int $connectionId 连接ID
     * @param string $tableName 表名
     * @param string|int $id 主键值
     * @return object|null 数据对象或 null
     */
    public static function getRow(int $connectionId, string $tableName, string|int $id): ?object
    {
        // 获取连接
        $connection = Connection::find($connectionId);
        if (!$connection) {
            return null;
        }

        // 注册动态连接
        $connection->registerDynamicConnection();
        $connectionName = $connection->getDynamicConnectionName();

        // 获取主键字段名
        $primaryKey = self::getPrimaryKeyName($connectionName, $tableName);

        return DB::connection($connectionName)->table($tableName)->where($primaryKey, $id)->first();
    }

    /**
     * 插入数据
     *
     * 自动检测表是否有自增主键：
     * - 有自增主键：使用 insertGetId() 返回 ID
     * - 无自增主键：使用 insert() 返回是否成功
     *
     * @param int $connectionId 连接ID
     * @param string $tableName 表名
     * @param array<string, mixed> $data 插入数据
     * @return int 插入的 ID（如果有自增主键），否则返回 1 表示成功，0 表示失败
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

        // 检查表是否有自增主键
        $hasAutoIncrement = self::hasAutoIncrementColumn($connectionName, $tableName);

        if ($hasAutoIncrement) {
            // 有自增主键：返回插入的 ID
            return DB::connection($connectionName)->table($tableName)->insertGetId($data);
        } else {
            // 无自增主键：返回是否成功
            $success = DB::connection($connectionName)->table($tableName)->insert($data);
            return $success ? 1 : 0;
        }
    }

    /**
     * 检查表是否有自增主键
     *
     * @param string $connectionName 连接名称
     * @param string $tableName 表名
     * @return bool
     */
    private static function hasAutoIncrementColumn(string $connectionName, string $tableName): bool
    {
        // 获取连接的驱动类型
        $driver = config("database.connections.{$connectionName}.driver");

        switch ($driver) {
            case 'mysql':
                // MySQL: 检查 EXTRA 字段是否包含 auto_increment
                $result = DB::connection($connectionName)->selectOne("
                    SELECT COUNT(*) as count
                    FROM information_schema.COLUMNS
                    WHERE table_schema = DATABASE()
                    AND table_name = ?
                    AND EXTRA = 'auto_increment'
                ", [$tableName]);
                return ($result->count ?? 0) > 0;

            case 'pgsql':
                // PostgreSQL: 检查默认值是否包含 nextval
                $result = DB::connection($connectionName)->selectOne("
                    SELECT COUNT(*) as count
                    FROM information_schema.columns
                    WHERE table_name = ?
                    AND column_default LIKE 'nextval%'
                ", [$tableName]);
                return ($result->count ?? 0) > 0;

            case 'sqlite':
                // SQLite: 检查是否有 INTEGER PRIMARY KEY
                $columns = DB::connection($connectionName)->select("PRAGMA table_info({$tableName})");
                foreach ($columns as $column) {
                    if ($column->pk && stripos($column->type, 'INTEGER') !== false) {
                        return true;
                    }
                }
                return false;

            default:
                return false;
        }
    }

    /**
     * 获取表的主键字段名（公开方法）
     *
     * @param string $connectionName 连接名称
     * @param string $tableName 表名
     * @return string 主键字段名，如果没有主键则返回 'id'
     */
    public static function getPrimaryKeyNamePublic(string $connectionName, string $tableName): string
    {
        return self::getPrimaryKeyName($connectionName, $tableName);
    }

    /**
     * 获取表的主键字段名
     *
     * @param string $connectionName 连接名称
     * @param string $tableName 表名
     * @return string 主键字段名，如果没有主键则返回 'id'
     */
    private static function getPrimaryKeyName(string $connectionName, string $tableName): string
    {
        // 获取连接的驱动类型
        $driver = config("database.connections.{$connectionName}.driver");

        switch ($driver) {
            case 'mysql':
                // MySQL: 从 information_schema 获取主键
                $result = DB::connection($connectionName)->selectOne("
                    SELECT COLUMN_NAME
                    FROM information_schema.COLUMNS
                    WHERE table_schema = DATABASE()
                    AND table_name = ?
                    AND COLUMN_KEY = 'PRI'
                    LIMIT 1
                ", [$tableName]);
                return $result->COLUMN_NAME ?? 'id';

            case 'pgsql':
                // PostgreSQL: 从约束获取主键
                $result = DB::connection($connectionName)->selectOne("
                    SELECT a.attname
                    FROM pg_index i
                    JOIN pg_attribute a ON a.attrelid = i.indrelid AND a.attnum = ANY(i.indkey)
                    WHERE i.indrelid = ?::regclass
                    AND i.indisprimary
                    LIMIT 1
                ", [$tableName]);
                return $result->attname ?? 'id';

            case 'sqlite':
                // SQLite: 从 PRAGMA table_info 获取主键
                $columns = DB::connection($connectionName)->select("PRAGMA table_info({$tableName})");
                foreach ($columns as $column) {
                    if ($column->pk) {
                        return $column->name;
                    }
                }
                return 'id';

            default:
                return 'id';
        }
    }

    /**
     * 更新数据
     *
     * 支持任意主键字段
     *
     * @param int $connectionId 连接ID
     * @param string $tableName 表名
     * @param string|int $id 主键值
     * @param array<string, mixed> $data 更新数据
     * @return bool 是否成功
     */
    public static function updateRow(int $connectionId, string $tableName, string|int $id, array $data): bool
    {
        // 获取连接
        $connection = Connection::find($connectionId);
        if (!$connection) {
            return false;
        }

        // 注册动态连接
        $connection->registerDynamicConnection();
        $connectionName = $connection->getDynamicConnectionName();

        // 获取主键字段名
        $primaryKey = self::getPrimaryKeyName($connectionName, $tableName);

        $affected = DB::connection($connectionName)->table($tableName)->where($primaryKey, $id)->update($data);

        return $affected > 0;
    }

    /**
     * 删除数据
     *
     * 支持任意主键字段
     *
     * @param int $connectionId 连接ID
     * @param string $tableName 表名
     * @param string|int $id 主键值
     * @return bool 是否成功
     */
    public static function deleteRow(int $connectionId, string $tableName, string|int $id): bool
    {
        // 获取连接
        $connection = Connection::find($connectionId);
        if (!$connection) {
            return false;
        }

        // 注册动态连接
        $connection->registerDynamicConnection();
        $connectionName = $connection->getDynamicConnectionName();

        // 获取主键字段名
        $primaryKey = self::getPrimaryKeyName($connectionName, $tableName);

        $deleted = DB::connection($connectionName)->table($tableName)->where($primaryKey, $id)->delete();

        return $deleted > 0;
    }
}
