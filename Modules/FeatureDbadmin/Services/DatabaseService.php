<?php

namespace Modules\FeatureDbadmin\Services;

use Modules\FeatureDbadmin\Models\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

/**
 * 数据库连接管理服务
 *
 * 管理数据库连接配置、测试连接、切换连接
 * 所有方法均为静态方法
 */
class DatabaseService
{
    /**
     * 获取所有数据库连接
     *
     * @param bool $activeOnly 是否只返回激活的连接
     * @return array
     */
    public static function getConnections(bool $activeOnly = true): array
    {
        $query = Connection::query();

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $query->orderBy('name')->get()->toArray();
    }

    /**
     * 获取连接配置
     *
     * @param int $connectionId 连接ID
     * @return array|null
     */
    public static function getConnectionConfig(int $connectionId): ?array
    {
        $connection = Connection::find($connectionId);

        if (!$connection) {
            return null;
        }

        return $connection->toConfigArray();
    }

    /**
     * 测试连接
     *
     * @param int $connectionId 连接ID
     * @return array{success: bool, message: string, version: string|null}
     */
    public static function testConnection(int $connectionId): array
    {
        $connection = Connection::find($connectionId);

        if (!$connection) {
            return [
                'success' => false,
                'message' => '连接不存在',
                'version' => null,
            ];
        }

        return $connection->testConnection();
    }

    /**
     * 测试连接配置（未保存的配置）
     *
     * @param array $config 连接配置
     * @return array{success: bool, message: string, version: string|null}
     */
    public static function testConnectionConfig(array $config): array
    {
        try {
            // 构建临时连接配置
            $tempConfig = [
                'driver' => $config['driver'],
                'host' => $config['host'] ?? '127.0.0.1',
                'port' => $config['port'] ?? ($config['driver'] === 'mysql' ? 3306 : 5432),
                'database' => $config['database'],
                'username' => $config['username'] ?? '',
                'password' => $config['password'] ?? '',
                'charset' => $config['charset'] ?? 'utf8mb4',
            ];

            // 创建临时连接名称
            $tempConnectionName = 'temp_test_' . uniqid();

            // 动态添加连接配置
            Config::set("database.connections.{$tempConnectionName}", $tempConfig);

            // 测试连接
            DB::purge($tempConnectionName);
            $pdo = DB::connection($tempConnectionName)->getPdo();

            // 获取版本
            $version = $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);

            // 清理临时连接
            DB::purge($tempConnectionName);

            return [
                'success' => true,
                'message' => '连接测试成功',
                'version' => $version,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '连接测试失败: ' . $e->getMessage(),
                'version' => null,
            ];
        }
    }

    /**
     * 切换到指定连接
     *
     * 注册动态连接并切换到该连接
     *
     * @param int $connectionId 连接ID
     * @return bool
     */
    public static function switchConnection(int $connectionId): bool
    {
        $connection = Connection::find($connectionId);

        if (!$connection) {
            return false;
        }

        $connectionName = $connection->getDynamicConnectionName();

        // 注册动态连接
        $connection->registerDynamicConnection();

        // 设置默认连接为动态连接
        Config::set('database.default', $connectionName);

        return true;
    }

    /**
     * 获取当前使用的连接名称
     *
     * @return string
     */
    public static function getCurrentConnection(): string
    {
        return Config::get('database.default', 'mysql');
    }

    /**
     * 获取连接的数据库版本
     *
     * @param int $connectionId 连接ID
     * @return string|null
     */
    public static function getDatabaseVersion(int $connectionId): ?string
    {
        $connection = Connection::find($connectionId);

        if (!$connection) {
            return null;
        }

        $connectionName = $connection->getDynamicConnectionName();

        // 注册动态连接
        $connection->registerDynamicConnection();

        // 尝试获取版本
        $version = null;

        switch ($connection->driver) {
            case 'mysql':
                $result = DB::connection($connectionName)->selectOne('SELECT VERSION() as version');
                $version = $result?->version;
                break;
            case 'pgsql':
                $result = DB::connection($connectionName)->selectOne('SELECT version() as version');
                $version = $result?->version;
                break;
            case 'sqlite':
                $result = DB::connection($connectionName)->selectOne('SELECT sqlite_version() as version');
                $version = $result?->version;
                break;
        }

        return $version;
    }

    /**
     * 获取动态连接名称
     *
     * 用于 TableService 等需要动态连接名称的场景
     *
     * @param int $connectionId 连接ID
     * @return string|null
     */
    public static function getDynamicConnectionName(int $connectionId): ?string
    {
        $connection = Connection::find($connectionId);

        if (!$connection) {
            return null;
        }

        return $connection->getDynamicConnectionName();
    }

    /**
     * 注册动态连接（不切换）
     *
     * 用于 TableService 等需要注册但不切换的场景
     *
     * @param int $connectionId 连接ID
     * @return bool
     */
    public static function registerConnection(int $connectionId): bool
    {
        $connection = Connection::find($connectionId);

        if (!$connection) {
            return false;
        }

        $connection->registerDynamicConnection();

        return true;
    }

    /**
     * 清理动态连接
     *
     * 用于清理不再使用的动态连接
     *
     * @param int $connectionId 连接ID
     * @return void
     */
    public static function purgeConnection(int $connectionId): void
    {
        $connection = Connection::find($connectionId);

        if (!$connection) {
            return;
        }

        $connectionName = $connection->getDynamicConnectionName();
        DB::purge($connectionName);
    }
}
