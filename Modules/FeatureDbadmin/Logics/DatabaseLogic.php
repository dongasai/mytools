<?php

namespace Modules\FeatureDbadmin\Logics;

/**
 * 数据库操作逻辑
 *
 * 提供数据库连接配置转换、驱动判断等逻辑
 */
class DatabaseLogic
{
    /**
     * 构建连接配置数组
     *
     * 根据驱动类型构建 Laravel 连接配置格式
     *
     * @param array $data 包含 driver, host, port, database, username, password, charset, collation, prefix 的数组
     * @return array Laravel 连接配置格式
     */
    public static function buildConnectionConfig(array $data): array
    {
        $driver = $data['driver'] ?? 'mysql';

        // SQLite 配置
        if ($driver === 'sqlite') {
            return [
                'driver' => 'sqlite',
                'database' => $data['database'] ?? ':memory:',
                'prefix' => $data['prefix'] ?? '',
                'foreign_key_constraints' => $data['foreign_key_constraints'] ?? true,
            ];
        }

        // MySQL/PostgreSQL 配置
        $config = [
            'driver' => $driver,
            'host' => $data['host'] ?? 'localhost',
            'port' => $data['port'] ?? self::getDefaultPort($driver),
            'database' => $data['database'] ?? '',
            'username' => $data['username'] ?? '',
            'password' => $data['password'] ?? '',
            'charset' => $data['charset'] ?? 'utf8mb4',
            'collation' => $data['collation'] ?? 'utf8mb4_unicode_ci',
            'prefix' => $data['prefix'] ?? '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ];

        // PostgreSQL 特殊配置
        if ($driver === 'pgsql') {
            $config['charset'] = $data['charset'] ?? 'utf8';
            unset($config['collation']);
            $config['schema'] = $data['schema'] ?? 'public';
            $config['sslmode'] = $data['sslmode'] ?? 'prefer';
        }

        return $config;
    }

    /**
     * 判断驱动是否支持
     *
     * 支持: mysql, pgsql, sqlite
     *
     * @param string $driver 驱动名称
     * @return bool 是否支持
     */
    public static function isDriverSupported(string $driver): bool
    {
        $supported = ['mysql', 'pgsql', 'sqlite'];

        return in_array(strtolower($driver), $supported, true);
    }

    /**
     * 获取驱动默认端口
     *
     * - MySQL: 3306
     * - PostgreSQL: 5432
     * - SQLite: 0（不使用端口）
     *
     * @param string $driver 驱动名称
     * @return int 默认端口
     */
    public static function getDefaultPort(string $driver): int
    {
        $ports = [
            'mysql' => 3306,
            'pgsql' => 5432,
            'sqlite' => 0,
        ];

        return $ports[strtolower($driver)] ?? 3306;
    }
}
