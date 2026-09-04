<?php

namespace Modules\ABase\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 请求日志配置检查服务
 *
 * 检查请求日志配置是否正确
 */
class RequestLogConfigCheckService
{
    /**
     * 检查请求日志配置
     *
     * @return array 问题列表
     */
    public static function checkConfigs(): array
    {
        $issues = [];

        // 检查是否启用请求日志
        $enabled = config('module_abase::abase.enabled', false);

        if (!$enabled) {
            // 未启用，不需要检查
            return [];
        }

        try {
            // 1. 检查 dblog 连接是否配置
            $dblogConfig = config('database.connections.dblog');

            if (!$dblogConfig) {
                $issues[] = '未配置 dblog 数据库连接，请在 config/database.php 中添加 dblog 连接配置';
                return $issues; // 如果没有配置，后续检查无意义
            }

            // 2. 检查 dblog 连接是否与主连接不同（建议独立）
            $defaultConnection = config('database.default');
            $defaultConfig = config("database.connections.{$defaultConnection}");

            $isSameHost = ($dblogConfig['host'] ?? null) === ($defaultConfig['host'] ?? null);
            $isSameDatabase = ($dblogConfig['database'] ?? null) === ($defaultConfig['database'] ?? null);

            if ($isSameHost && $isSameDatabase) {
                $issues[] = '建议：dblog 连接使用独立的数据库，当前与主数据库相同';
            }

            // 3. 尝试连接 dblog
            try {
                DB::connection('dblog')->getPdo();
            } catch (\Exception $e) {
                $issues[] = 'dblog 数据库连接失败: ' . $e->getMessage();
                return $issues; // 如果连接失败，后续检查无意义
            }

            // 4. 检查 sys_request_logs 表是否存在
            try {
                if (!Schema::connection('dblog')->hasTable('sys_request_logs')) {
                    $issues[] = 'sys_request_logs 表不存在，请执行迁移：php artisan migrate --path=Modules/ABase/Database/Migrations/2026_08_24_000001_create_sys_request_logs_table.php --database=dblog';
                    return $issues;
                }
            } catch (\Exception $e) {
                $issues[] = '检查 sys_request_logs 表时出错: ' . $e->getMessage();
                return $issues;
            }

            // 5. 检查表是否有数据（可选，仅提示）
            try {
                $count = DB::connection('dblog')->table('sys_request_logs')->count();

                if ($count === 0) {
                    $issues[] = '提示：sys_request_logs 表为空，尚未记录任何请求日志';
                } elseif ($count > 10000) {
                    $maxRecords = config('module_abase::abase.max_records', 100000);
                    if ($count > $maxRecords * 0.9) {
                        $issues[] = "提示：日志记录数量接近上限（{$count}/{$maxRecords}），将自动清理";
                    }
                }
            } catch (\Exception $e) {
                $issues[] = '查询日志记录数量时出错: ' . $e->getMessage();
            }

            // 6. 检查环境变量配置
            $envIssues = self::checkEnvConfig();
            $issues = array_merge($issues, $envIssues);

        } catch (\Exception $e) {
            $issues[] = '检查配置时发生错误: ' . $e->getMessage();
        }

        return $issues;
    }

    /**
     * 检查环境变量配置
     *
     * @return array
     */
    protected static function checkEnvConfig(): array
    {
        $issues = [];

        // 检查 REQUEST_LOG_MAX_RECORDS 配置
        $maxRecords = config('module_abase::abase.max_records', 100000);

        if ($maxRecords < 1000) {
            $issues[] = "REQUEST_LOG_MAX_RECORDS 设置过小（{$maxRecords}），建议至少 1000";
        } elseif ($maxRecords > 1000000) {
            $issues[] = "REQUEST_LOG_MAX_RECORDS 设置过大（{$maxRecords}），建议不超过 1000000";
        }

        return $issues;
    }

    /**
     * 获取问题数量
     *
     * @return int
     */
    public static function getIssueCount(): int
    {
        return count(self::checkConfigs());
    }

    /**
     * 获取检查结果摘要
     *
     * @return array
     */
    public static function getSummary(): array
    {
        $enabled = config('module_abase::abase.enabled', false);

        if (!$enabled) {
            return [
                'enabled' => false,
                'total' => 0,
                'issues' => [],
                'status' => 'info',
                'message' => '请求日志功能未启用',
            ];
        }

        $issues = self::checkConfigs();

        return [
            'enabled' => true,
            'total' => count($issues),
            'issues' => $issues,
            'status' => empty($issues) ? 'success' : 'warning',
            'message' => empty($issues) ? '请求日志配置正常' : '请求日志配置存在问题',
        ];
    }

    /**
     * 获取配置信息
     *
     * @return array
     */
    public static function getConfigInfo(): array
    {
        return [
            'enabled' => config('module_abase::abase.enabled', false),
            'max_records' => config('module_abase::abase.max_records', 100000),
            'connection' => 'dblog',
            'table' => 'sys_request_logs',
        ];
    }

    /**
     * 获取当前日志条数
     *
     * @return int
     */
    public static function getCurrentLogCount(): int
    {
        try {
            return DB::connection('dblog')->table('sys_request_logs')->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
}