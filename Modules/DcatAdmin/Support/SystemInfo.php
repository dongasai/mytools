<?php

namespace Modules\DcatAdmin\Support;

use Illuminate\Support\Facades\DB;

/**
 * 系统信息工具类
 */
class SystemInfo
{
    /**
     * 获取服务器信息
     */
    public function getServerInfo(): array
    {
        return [
            'os' => php_uname('s'),
            'os_version' => php_uname('r'),
            'hostname' => php_uname('n'),
            'architecture' => php_uname('m'),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'server_ip' => $_SERVER['SERVER_ADDR'] ?? 'Unknown',
            'server_port' => $_SERVER['SERVER_PORT'] ?? 'Unknown',
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
        ];
    }

    /**
     * 获取PHP信息
     */
    public function getPhpInfo(): array
    {
        return [
            'version' => PHP_VERSION,
            'sapi' => php_sapi_name(),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'timezone' => date_default_timezone_get(),
            'extensions' => $this->getLoadedExtensions(),
        ];
    }

    /**
     * 获取已加载的PHP扩展
     */
    protected function getLoadedExtensions(): array
    {
        $extensions = get_loaded_extensions();
        sort($extensions);

        return $extensions;
    }

    /**
     * 获取数据库信息
     */
    public function getDatabaseInfo(): array
    {
        try {
            $connection = DB::connection();
            $pdo = $connection->getPdo();

            return [
                'driver' => $connection->getDriverName(),
                'version' => $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION),
                'database' => $connection->getDatabaseName(),
                'charset' => $connection->getConfig('charset'),
                'collation' => $connection->getConfig('collation'),
                'prefix' => $connection->getConfig('prefix'),
                'status' => 'connected',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * 获取内存使用情况
     */
    public function getMemoryUsage(): array
    {
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);
        $memoryLimit = $this->parseMemoryLimit(ini_get('memory_limit'));

        return [
            'current' => $this->formatBytes($memoryUsage),
            'peak' => $this->formatBytes($memoryPeak),
            'limit' => $this->formatBytes($memoryLimit),
            'percentage' => $memoryLimit > 0 ? round(($memoryUsage / $memoryLimit) * 100, 2) : 0,
            'current_bytes' => $memoryUsage,
            'peak_bytes' => $memoryPeak,
            'limit_bytes' => $memoryLimit,
        ];
    }

    /**
     * 解析内存限制
     */
    protected function parseMemoryLimit(string $memoryLimit): int
    {
        if ($memoryLimit === '-1') {
            return 0; // 无限制
        }

        $unit = strtolower(substr($memoryLimit, -1));
        $value = (int) substr($memoryLimit, 0, -1);

        switch ($unit) {
            case 'g':
                return $value * 1024 * 1024 * 1024;
            case 'm':
                return $value * 1024 * 1024;
            case 'k':
                return $value * 1024;
            default:
                return (int) $memoryLimit;
        }
    }

    /**
     * 格式化字节数
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = floor(log($bytes, 1024));

        return round($bytes / pow(1024, $power), 2).' SystemInfo.php'.$units[$power];
    }

    /**
     * 获取磁盘使用情况
     */
    public function getDiskUsage(): array
    {
        $path = base_path();
        $totalBytes = disk_total_space($path);
        $freeBytes = disk_free_space($path);
        $usedBytes = $totalBytes - $freeBytes;

        return [
            'total' => $this->formatBytes($totalBytes),
            'used' => $this->formatBytes($usedBytes),
            'free' => $this->formatBytes($freeBytes),
            'percentage' => $totalBytes > 0 ? round(($usedBytes / $totalBytes) * 100, 2) : 0,
            'total_bytes' => $totalBytes,
            'used_bytes' => $usedBytes,
            'free_bytes' => $freeBytes,
        ];
    }

    /**
     * 获取CPU使用率（Linux系统）
     */
    public function getCpuUsage(): float
    {
        if (PHP_OS_FAMILY !== 'Linux') {
            return 0.0;
        }

        try {
            $load = sys_getloadavg();

            return $load ? round($load[0] * 100, 2) : 0.0;
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    /**
     * 获取系统负载平均值
     */
    public function getLoadAverage(): array
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();

            return [
                '1min' => round($load[0], 2),
                '5min' => round($load[1], 2),
                '15min' => round($load[2], 2),
            ];
        }

        return [
            '1min' => 0,
            '5min' => 0,
            '15min' => 0,
        ];
    }

    /**
     * 获取系统运行时间
     */
    public function getUptime(): string
    {
        if (PHP_OS_FAMILY === 'Linux') {
            try {
                $uptime = file_get_contents('/proc/uptime');
                $seconds = (int) explode(' ', $uptime)[0];

                $days = floor($seconds / 86400);
                $hours = floor(($seconds % 86400) / 3600);
                $minutes = floor(($seconds % 3600) / 60);

                return "{$days}天 {$hours}小时 {$minutes}分钟";
            } catch (\Exception $e) {
                return 'Unknown';
            }
        }

        return 'Unknown';
    }

    /**
     * 获取数据库状态
     */
    public function getDatabaseStatus(): array
    {
        try {
            $startTime = microtime(true);
            DB::select('SELECT 1');
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            return [
                'status' => 'healthy',
                'response_time' => $responseTime.'ms',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * 获取性能指标
     */
    public function getPerformanceMetrics(): array
    {
        return [
            'memory' => $this->getMemoryUsage(),
            'disk' => $this->getDiskUsage(),
            'cpu_usage' => $this->getCpuUsage(),
            'load_average' => $this->getLoadAverage(),
            'database' => $this->getDatabaseStatus(),
        ];
    }

    /**
     * 获取Laravel应用信息
     */
    public function getApplicationInfo(): array
    {
        return [
            'name' => config('app.name'),
            'env' => config('app.env'),
            'debug' => config('app.debug'),
            'url' => config('app.url'),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
        ];
    }
}
