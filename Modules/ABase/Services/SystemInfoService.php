<?php

namespace Modules\ABase\Services;

/**
 * 系统信息服务
 *
 * 收集并返回系统版本信息，供 CLI 命令和后台页面共用
 */
class SystemInfoService
{
    /**
     * 获取所有系统信息
     */
    public static function getAll(): array
    {
        return [
            'build' => static::getBuildInfo(),
            'framework' => static::getFrameworkInfo(),
            'runtime' => static::getRuntimeInfo(),
            'database' => static::getDatabaseInfo(),
        ];
    }

    /**
     * 获取构建信息（从 Docker 环境变量读取）
     */
    public static function getBuildInfo(): array
    {
        $buildTimeRaw = env('DOCKER_BUILD_TIME', 'N/A');

        return [
            'build_time_utc' => $buildTimeRaw,
            'build_time_local' => static::formatBuildTime($buildTimeRaw),
            'build_branch' => env('DOCKER_BUILD_BRANCH', 'N/A'),
            'build_commit' => env('DOCKER_BUILD_COMMIT', 'N/A'),
            'build_runner' => env('DOCKER_BUILD_RUNNER', 'N/A'),
        ];
    }

    /**
     * 获取框架信息
     */
    public static function getFrameworkInfo(): array
    {
        return [
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'app_name' => config('app.name'),
        ];
    }

    /**
     * 获取运行环境信息
     */
    public static function getRuntimeInfo(): array
    {
        return [
            'environment' => config('app.env'),
            'timezone' => date_default_timezone_get(),
            'os' => PHP_OS_FAMILY,
            'sapi' => php_sapi_name(),
        ];
    }

    /**
     * 获取数据库信息
     */
    public static function getDatabaseInfo(): array
    {
        $connection = config('database.default');
        $dbName = config("database.connections.{$connection}.database", 'N/A');

        $dbVersion = '无法获取';
        $pdo = null;
        try {
            $pdo = app('db')->connection()->getPdo();
            $dbVersion = $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);
        } catch (\Exception) {
            // 忽略连接失败
        }

        return [
            'connection' => $connection,
            'database_name' => $dbName,
            'database_version' => $dbVersion,
            'composer_version' => static::getComposerVersion(),
        ];
    }

    /**
     * 格式化构建时间为 Y-m-d H:i:s
     */
    private static function formatBuildTime(?string $time): string
    {
        if (! $time || $time === 'N/A') {
            return 'N/A';
        }

        try {
            $dt = new \DateTime($time);
            $dt->setTimezone(new \DateTimeZone(date_default_timezone_get()));

            return $dt->format('Y-m-d H:i:s');
        } catch (\Exception) {
            return $time;
        }
    }

    /**
     * 获取 Composer 版本
     */
    private static function getComposerVersion(): string
    {
        $output = [];
        exec('composer --version 2>&1', $output, $returnCode);
        if ($returnCode === 0 && ! empty($output)) {
            return trim(str_replace('Composer version', '', $output[0]));
        }

        return 'N/A';
    }
}
