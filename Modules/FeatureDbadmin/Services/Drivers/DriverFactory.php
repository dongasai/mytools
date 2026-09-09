<?php

namespace Modules\FeatureDbadmin\Services\Drivers;

use Modules\FeatureDbadmin\Models\Connection;

/**
 * 数据库驱动工厂
 *
 * 根据连接配置创建对应的数据库驱动实例
 */
class DriverFactory
{
    /**
     * 创建数据库驱动实例
     *
     * @param Connection $connection 数据库连接
     * @return DatabaseDriverInterface
     * @throws \InvalidArgumentException 不支持的数据库类型
     */
    public static function create(Connection $connection): DatabaseDriverInterface
    {
        // 注册动态连接
        $connection->registerDynamicConnection();
        $connectionName = $connection->getDynamicConnectionName();

        return match ($connection->driver) {
            'mysql' => new MySqlDriver($connectionName),
            'pgsql' => new PgSqlDriver($connectionName),
            'sqlite' => new SqliteDriver($connectionName),
            default => throw new \InvalidArgumentException("不支持的数据库类型: {$connection->driver}"),
        };
    }

    /**
     * 根据连接ID创建驱动实例
     *
     * @param int $connectionId 连接ID
     * @return DatabaseDriverInterface
     * @throws \InvalidArgumentException 连接不存在
     */
    public static function createFromId(int $connectionId): DatabaseDriverInterface
    {
        $connection = Connection::find($connectionId);

        if (!$connection) {
            throw new \InvalidArgumentException("连接不存在: ID {$connectionId}");
        }

        return self::create($connection);
    }
}