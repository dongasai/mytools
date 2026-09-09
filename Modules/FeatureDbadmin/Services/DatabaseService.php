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
        // 校验 driver 参数
        if (!isset($config['driver'])) {
            return [
                'success' => false,
                'message' => '缺少 driver 参数',
                'version' => null,
            ];
        }

        // 校验 driver 合法性
        $supportedDrivers = ['mysql', 'mariadb', 'pgsql', 'sqlite'];
        if (!in_array($config['driver'], $supportedDrivers, true)) {
            return [
                'success' => false,
                'message' => '不支持的数据库驱动: ' . $config['driver'],
                'version' => null,
            ];
        }

        // 根据驱动类型确定正确的字符集
        $correctCharset = match ($config['driver']) {
            'pgsql' => 'utf8',  // PostgreSQL 只支持 utf8
            'mysql', 'mariadb' => $config['charset'] ?? 'utf8mb4',  // MySQL/MariaDB 使用用户指定的或默认 utf8mb4
            'sqlite' => null,  // SQLite 不需要 charset
            default => $config['charset'] ?? 'utf8mb4',
        };

        // 构建临时连接配置
        $tempConfig = [
            'driver' => $config['driver'],
            'database' => $config['database'],
        ];

        // SQLite 不需要 host/port/username/password
        if ($config['driver'] !== 'sqlite') {
            $tempConfig['host'] = $config['host'] ?? '127.0.0.1';
            // 修复 MariaDB 端口默认值：MySQL 和 MariaDB 都使用 3306
            $tempConfig['port'] = $config['port'] ?? (in_array($config['driver'], ['mysql', 'mariadb'], true) ? 3306 : 5432);
            $tempConfig['username'] = $config['username'] ?? '';
            $tempConfig['password'] = $config['password'] ?? '';
        }

        // 只在需要字符集的驱动中添加
        if ($correctCharset !== null) {
            $tempConfig['charset'] = $correctCharset;
        }

        // MySQL/MariaDB 排序规则
        if (in_array($config['driver'], ['mysql', 'mariadb'], true) && isset($config['collation'])) {
            $tempConfig['collation'] = $config['collation'];
        }

        // PostgreSQL 特殊配置
        if ($config['driver'] === 'pgsql') {
            $tempConfig['schema'] = $config['schema'] ?? 'public';
            $tempConfig['sslmode'] = $config['sslmode'] ?? 'prefer';
        }

        // 创建临时连接名称
        $tempConnectionName = 'temp_test_' . uniqid();

        try {
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
            // 清理临时连接配置
            DB::purge($tempConnectionName);

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
