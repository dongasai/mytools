<?php

namespace Modules\ABase\Services;

use Modules\ABase\Enums\ConfigDbBackupMode;
use Modules\ABase\Enums\ConfigDbCompressionType;
use Modules\ABase\Enums\ConfigDbNotificationChannel;
use Modules\ABase\Enums\ConfigDbType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

/**
 * 配置表备份服务
 */
class ConfigDbBackupService
{
    /**
     * 扫描所有模块的配置表配置
     */
    public function scanModuleConfigs(): array
    {
        $modulesPath = base_path('Modules');
        $moduleConfigs = [];

        if (! is_dir($modulesPath)) {
            return $moduleConfigs;
        }

        $modules = array_filter(scandir($modulesPath), function ($dir) use ($modulesPath) {
            return $dir !== '.' && $dir !== '..' && is_dir($modulesPath . '/' . $dir);
        });

        foreach ($modules as $module) {
            // 支持多种配置文件名
            $configFiles = [
                'configdb.php',
                'configdb_with_enums.php',
                'configdb_example.php',
            ];

            foreach ($configFiles as $configFile) {
                $configPath = $modulesPath . '/' . $module . '/config/' . $configFile;

                if (file_exists($configPath)) {
                    $config = include $configPath;

                    if (is_array($config) && ($config['enabled'] ?? true)) {
                        // 验证配置中的枚举值
                        $validatedConfig = $this->validateConfigEnums($config);
                        $moduleConfigs[$module] = $validatedConfig;
                        break; // 找到第一个配置文件就停止
                    }
                }
            }
        }

        return $moduleConfigs;
    }

    /**
     * 验证配置中的枚举值
     */
    private function validateConfigEnums(array $config): array
    {
        // 验证表配置中的类型
        if (isset($config['tables'])) {
            foreach ($config['tables'] as $tableName => $tableConfig) {
                if (isset($tableConfig['type'])) {
                    if (! ConfigDbType::isValid($tableConfig['type'])) {
                        throw new \InvalidArgumentException(
                            "表 {$tableName} 的类型 '{$tableConfig['type']}' 无效。" .
                                '有效类型: ' . implode(', ', array_column(ConfigDbType::cases(), 'value'))
                        );
                    }
                }

                // 验证备份模式
                if (isset($tableConfig['backup_mode'])) {
                    if (! ConfigDbBackupMode::isValid($tableConfig['backup_mode'])) {
                        throw new \InvalidArgumentException(
                            "表 {$tableName} 的备份模式 '{$tableConfig['backup_mode']}' 无效。" .
                                '有效模式: ' . implode(', ', array_column(ConfigDbBackupMode::cases(), 'value'))
                        );
                    }
                }
            }
        }

        // 验证全局设置中的枚举值
        if (isset($config['global_settings'])) {
            $globalSettings = $config['global_settings'];

            // 验证备份模式
            if (isset($globalSettings['backup_mode'])) {
                if (! ConfigDbBackupMode::isValid($globalSettings['backup_mode'])) {
                    throw new \InvalidArgumentException(
                        "全局备份模式 '{$globalSettings['backup_mode']}' 无效。" .
                            '有效模式: ' . implode(', ', array_column(ConfigDbBackupMode::cases(), 'value'))
                    );
                }
            }

            // 验证压缩类型
            if (isset($globalSettings['compression']['type'])) {
                if (! ConfigDbCompressionType::isValid($globalSettings['compression']['type'])) {
                    throw new \InvalidArgumentException(
                        "压缩类型 '{$globalSettings['compression']['type']}' 无效。" .
                            '有效类型: ' . implode(', ', array_column(ConfigDbCompressionType::cases(), 'value'))
                    );
                }
            }

            // 验证通知渠道
            if (isset($globalSettings['notification_channels'])) {
                foreach ($globalSettings['notification_channels'] as $channel) {
                    if (! ConfigDbNotificationChannel::isValid($channel)) {
                        throw new \InvalidArgumentException(
                            "通知渠道 '{$channel}' 无效。" .
                                '有效渠道: ' . implode(', ', array_column(ConfigDbNotificationChannel::cases(), 'value'))
                        );
                    }
                }
            }
        }

        // 验证高级设置中的枚举值
        if (isset($config['advanced_settings'])) {
            $advancedSettings = $config['advanced_settings'];

            // 验证压缩类型
            if (isset($advancedSettings['compression']['type'])) {
                if (! ConfigDbCompressionType::isValid($advancedSettings['compression']['type'])) {
                    throw new \InvalidArgumentException(
                        "高级设置中的压缩类型 '{$advancedSettings['compression']['type']}' 无效。" .
                            '有效类型: ' . implode(', ', array_column(ConfigDbCompressionType::cases(), 'value'))
                    );
                }
            }

            // 验证通知渠道
            if (isset($advancedSettings['notifications']['channels'])) {
                foreach ($advancedSettings['notifications']['channels'] as $channel) {
                    if (! ConfigDbNotificationChannel::isValid($channel)) {
                        throw new \InvalidArgumentException(
                            "高级设置中的通知渠道 '{$channel}' 无效。" .
                                '有效渠道: ' . implode(', ', array_column(ConfigDbNotificationChannel::cases(), 'value'))
                        );
                    }
                }
            }
        }

        return $config;
    }

    /**
     * 解析表配置，返回需要备份的表列表
     */
    public function parseTableConfig(array $config): array
    {
        $tables = [];
        $globalSettings = $config['global_settings'] ?? [];

        foreach ($config['tables'] ?? [] as $key => $tableConfig) {
            $type = $tableConfig['type'] ?? 'model';

            if ($type === 'model') {
                $tables = array_merge($tables, $this->parseModelTable($tableConfig, $globalSettings));
            } elseif ($type === 'prefix') {
                $tables = array_merge($tables, $this->parsePrefixTables($tableConfig, $globalSettings));
            }
        }

        return $tables;
    }

    /**
     * 解析模型类型的表配置
     */
    private function parseModelTable(array $config, array $globalSettings): array
    {
        $model = $config['model'] ?? null;

        if (! $model || ! class_exists($model)) {
            return [];
        }

        $modelInstance = new $model;
        $tableName = $modelInstance->getTable();

        return [
            $tableName => [
                'model' => $model,
                'description' => $config['description'] ?? $tableName,
                'condition' => $config['condition'] ?? null,
                'order_by' => $config['order_by'] ?? $globalSettings['default_order_by'] ?? ['id', 'asc'],
                'exclude_columns' => array_merge(
                    $globalSettings['default_exclude_columns'] ?? [],
                    $config['exclude_columns'] ?? []
                ),
                'only_columns' => $config['only_columns'] ?? [],
                'type' => 'model',
            ],
        ];
    }

    /**
     * 解析前缀类型的表配置
     */
    private function parsePrefixTables(array $config, array $globalSettings): array
    {
        $prefix = $config['prefix'] ?? '';
        $excludeTables = $config['exclude_tables'] ?? [];
        $tables = [];

        if (empty($prefix)) {
            return $tables;
        }

        // 获取所有表名
        $allTables = Schema::getTableListing();

        foreach ($allTables as $tableName) {
            if (str_starts_with($tableName, $prefix) && ! in_array($tableName, $excludeTables)) {
                $tables[$tableName] = [
                    'description' => ($config['description'] ?? '前缀表') . ' - ' . $tableName,
                    'condition' => $config['condition'] ?? null,
                    'order_by' => $config['order_by'] ?? $globalSettings['default_order_by'] ?? ['id', 'asc'],
                    'exclude_columns' => array_merge(
                        $globalSettings['default_exclude_columns'] ?? [],
                        $config['exclude_columns'] ?? []
                    ),
                    'only_columns' => $config['only_columns'] ?? [],
                    'type' => 'prefix',
                    'prefix' => $prefix,
                ];
            }
        }

        return $tables;
    }

    /**
     * 生成单个模块的备份SQL
     */
    public function generateModuleBackup(string $moduleName, array $config): string
    {
        $tables = $this->parseTableConfig($config);
        $globalSettings = $config['global_settings'] ?? [];

        $sql = '';

        // 添加模块头部信息
        if ($globalSettings['add_module_header'] ?? true) {
            $sql .= $this->generateModuleHeader($moduleName, $config);
        }

        $totalTables = 0;
        $totalRecords = 0;

        foreach ($tables as $tableName => $tableConfig) {
            try {
                $tableSql = $this->generateTableSql($tableName, $tableConfig);
                if (! empty($tableSql)) {
                    $sql .= $tableSql;
                    $totalTables++;
                    $totalRecords += $this->getTableRecordCount($tableName, $tableConfig);
                }
            } catch (\Exception $e) {
                // 记录错误但继续处理其他表
                $sql .= "-- 错误：处理表 {$tableName} 时失败: " . $e->getMessage() . "\n\n";
            }
        }

        // 添加统计信息
        if ($globalSettings['add_statistics'] ?? true) {
            $sql .= $this->generateModuleFooter($totalTables, $totalRecords);
        }

        return $sql;
    }

    /**
     * 生成模块头部信息
     */
    private function generateModuleHeader(string $moduleName, array $config): string
    {
        $moduleDisplayName = $config['module_name'] ?? $moduleName;
        $header = '';

        $header .= "-- =============================================================\n";
        $header .= "-- 模块: {$moduleDisplayName} ({$moduleName})\n";
        $header .= '-- 备份时间: ' . now()->toDateTimeString() . "\n";
        $header .= '-- 描述: ' . ($config['description'] ?? '配置表备份') . "\n";
        $header .= "-- 警告: 此文件由系统自动生成，禁止修改！\n";
        $header .= "-- =============================================================\n\n";

        return $header;
    }

    /**
     * 生成单个表的SQL
     */
    private function generateTableSql(string $tableName, array $tableConfig): string
    {
        if (! Schema::hasTable($tableName)) {
            return "-- 错误：表 {$tableName} 不存在\n\n";
        }

        $sql = '';
        $sql .= "-- ==========================================\n";
        $sql .= "-- 表: {$tableName}\n";
        $sql .= "-- 描述: {$tableConfig['description']}\n";
        $sql .= "-- ==========================================\n\n";

        // 添加建表语句
        $createTableSql = $this->getCreateTableSql($tableName);
        $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
        $sql .= "{$createTableSql};\n\n";

        // 添加数据插入语句
        $dataSql = $this->generateInsertSql($tableName, $tableConfig);
        $sql .= $dataSql;

        return $sql;
    }

    /**
     * 获取建表语句
     */
    private function getCreateTableSql(string $tableName): string
    {
        $result = DB::select("SHOW CREATE TABLE `{$tableName}`");

        if (empty($result)) {
            throw new \Exception("无法获取表 {$tableName} 的创建语句");
        }

        $createSql = $result[0]->{'Create Table'};

        // 移除AUTO_INCREMENT值
        $createSql = preg_replace('/\s+AUTO_INCREMENT=\d+/', '', $createSql);

        return $createSql;
    }

    /**
     * 生成INSERT语句
     */
    private function generateInsertSql(string $tableName, array $tableConfig): string
    {
        $query = DB::table($tableName);

        // 应用条件
        if (! empty($tableConfig['condition'])) {
            $query->where($tableConfig['condition']);
        }

        // 应用排序
        if (! empty($tableConfig['order_by'])) {
            $query->orderBy(...$tableConfig['order_by']);
        }

        $records = $query->get();

        if ($records->isEmpty()) {
            return "-- 该表无数据记录\n\n";
        }

        // 处理字段
        $firstRecord = (array) $records->first();
        $allColumns = array_keys($firstRecord);

        // 应用字段过滤
        $excludeColumns = $tableConfig['exclude_columns'] ?? [];
        $onlyColumns = $tableConfig['only_columns'] ?? [];

        if (! empty($onlyColumns)) {
            $columns = array_intersect($allColumns, $onlyColumns);
        } else {
            $columns = array_diff($allColumns, $excludeColumns);
        }

        if (empty($columns)) {
            return "-- 没有可导出的字段\n\n";
        }

        $columnList = '`' . implode('`, `', $columns) . '`';

        $sql = "-- 数据插入\n";
        $sql .= "INSERT INTO `{$tableName}` ({$columnList}) VALUES\n";

        $values = [];
        foreach ($records as $record) {
            $recordArray = (array) $record;
            $escapedValues = [];

            foreach ($columns as $column) {
                $value = $recordArray[$column] ?? null;
                $escapedValues[] = $this->escapeValue($value);
            }

            $values[] = '(' . implode(', ', $escapedValues) . ')';
        }

        $sql .= implode(",\n", $values) . ";\n\n";

        return $sql;
    }

    /**
     * 转义值
     */
    private function escapeValue($value): string
    {
        if ($value === null) {
            return 'NULL';
        } elseif (is_numeric($value) && ! is_string($value)) {
            return (string) $value;
        } elseif (is_bool($value)) {
            return $value ? '1' : '0';
        } else {
            return "'" . addslashes((string) $value) . "'";
        }
    }

    /**
     * 获取表记录数
     */
    private function getTableRecordCount(string $tableName, array $tableConfig): int
    {
        $query = DB::table($tableName);

        if (! empty($tableConfig['condition'])) {
            $query->where($tableConfig['condition']);
        }

        return $query->count();
    }

    /**
     * 生成模块尾部统计信息
     */
    private function generateModuleFooter(int $totalTables, int $totalRecords): string
    {
        $footer = '';
        $footer .= "-- ==========================================\n";
        $footer .= "-- 模块备份统计\n";
        $footer .= "-- 备份表数: {$totalTables}\n";
        $footer .= "-- 总记录数: {$totalRecords}\n";
        $footer .= '-- 完成时间: ' . now()->toDateTimeString() . "\n";
        $footer .= "-- ==========================================\n";

        return $footer;
    }

    /**
     * 保存备份文件
     */
    public function saveBackupFile(string $moduleName, string $content): string
    {
        $outputDir = database_path('sql/modules');

        if (! File::exists($outputDir)) {
            File::makeDirectory($outputDir, 0755, true);
        }

        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "{$moduleName}_configdb_{$timestamp}.sql";
        $filePath = $outputDir . '/' . $filename;

        File::put($filePath, $content);

        return $filePath;
    }
}
