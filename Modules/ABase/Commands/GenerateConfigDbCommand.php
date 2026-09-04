<?php

namespace Modules\ABase\Commands;

use Modules\ABase\Services\ConfigDbBackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * 生成配置表的数据库备份文件
 *
 * php artisan abase:generate:configdb
 * php artisan abase:generate:configdb --module=ModuleName
 * php artisan abase:generate:configdb --list
 */
class GenerateConfigDbCommand extends Command
{
    protected $signature = 'abase:generate:configdb
                           {--module= : 指定要备份的模块名称，不指定则备份所有模块}
                           {--list : 列出所有可用的模块配置}
                           {--output-dir= : 指定输出目录，默认为 database/sql/modules}';

    protected $description = '生成配置表的数据库备份文件，支持模块化配置和分文件输出';

    private ConfigDbBackupService $backupService;

    public function __construct(ConfigDbBackupService $backupService)
    {
        parent::__construct();
        $this->backupService = $backupService;
    }

    public function handle()
    {
        // 列出可用模块
        if ($this->option('list')) {
            $this->listAvailableModules();

            return self::SUCCESS;
        }

        $this->info('配置表数据库备份工具 v2.0');
        $this->info('===========================================');

        $outputDir = $this->option('output-dir') ?: database_path('sql/modules');

        // 确保输出目录存在
        if (! File::exists($outputDir)) {
            File::makeDirectory($outputDir, 0755, true);
            $this->info("创建输出目录: {$outputDir}");
        }

        $startTime = microtime(true);
        $totalModules = 0;
        $totalTables = 0;
        $totalRecords = 0;
        $generatedFiles = [];

        // 扫描模块配置
        $moduleConfigs = $this->backupService->scanModuleConfigs();

        if (empty($moduleConfigs)) {
            $this->warn('未找到任何模块的配置表配置文件');
            $this->info('请确保模块目录下存在 config/configdb.php 文件');

            return self::SUCCESS;
        }

        // 处理指定模块或所有模块
        $targetModule = $this->option('module');
        $modulesToProcess = $targetModule
            ? (isset($moduleConfigs[$targetModule]) ? [$targetModule => $moduleConfigs[$targetModule]] : [])
            : $moduleConfigs;

        if ($targetModule && empty($modulesToProcess)) {
            $this->error("未找到模块 '{$targetModule}' 的配置");
            $this->info('可用的模块:');
            foreach (array_keys($moduleConfigs) as $moduleName) {
                $this->info("  - {$moduleName}");
            }

            return self::FAILURE;
        }

        $this->info('开始处理模块备份...');

        foreach ($modulesToProcess as $moduleName => $config) {
            $this->line("\n处理模块: {$moduleName}");

            try {
                $this->line('  正在生成SQL内容...');
                $sqlContent = $this->backupService->generateModuleBackup($moduleName, $config);

                if (empty(trim($sqlContent))) {
                    $this->warn("  模块 {$moduleName} 没有可备份的表");

                    continue;
                }

                $this->line('  正在保存备份文件...');
                $filePath = $this->backupService->saveBackupFile($moduleName, $sqlContent);

                $generatedFiles[] = $filePath;
                $totalModules++;

                // 统计信息
                $tableCount = substr_count($sqlContent, 'CREATE TABLE');
                $recordCount = substr_count($sqlContent, 'INSERT INTO');

                $totalTables += $tableCount;
                $totalRecords += $this->estimateRecordCount($sqlContent);

                $this->info("  ✓ 完成: {$filePath}");
                $this->info("    备份表数: {$tableCount}");
                $this->info('    估算记录数: ' . number_format($this->estimateRecordCount($sqlContent)));
            } catch (\Exception $e) {
                $this->error("  ✗ 处理模块 {$moduleName} 时出错: " . $e->getMessage());

                continue;
            }
        }

        $endTime = microtime(true);
        $executionTime = round($endTime - $startTime, 2);

        // 输出汇总信息
        $this->line("\n" . str_repeat('=', 50));
        $this->info('备份完成统计:');
        $this->line("  处理模块数: {$totalModules}");
        $this->line("  备份表总数: {$totalTables}");
        $this->line('  总记录数: ' . number_format($totalRecords));
        $this->line("  执行时间: {$executionTime}秒");
        $this->line("  输出目录: {$outputDir}");

        if (! empty($generatedFiles)) {
            $this->line("\n生成的文件:");
            foreach ($generatedFiles as $file) {
                $size = $this->formatBytes(File::size($file));
                $this->line('  - ' . basename($file) . " ({$size})");
            }
        }

        $this->line("\n" . str_repeat('=', 50));
        $this->info('配置表备份完成!');

        return self::SUCCESS;
    }

    /**
     * 列出所有可用的模块配置
     */
    private function listAvailableModules(): void
    {
        $this->info('可用模块配置:');

        $moduleConfigs = $this->backupService->scanModuleConfigs();

        if (empty($moduleConfigs)) {
            $this->warn('未找到任何模块配置');

            return;
        }

        foreach ($moduleConfigs as $moduleName => $config) {
            $displayName = $config['module_name'] ?? $moduleName;
            $description = $config['description'] ?? '无描述';
            $tables = $this->backupService->parseTableConfig($config);
            $tableCount = count($tables);

            $this->line("\n  模块: {$moduleName} ({$displayName})");
            $this->line("    描述: {$description}");
            $this->line("    配置表数量: {$tableCount}");

            if ($tableCount > 0 && $this->output->isVerbose()) {
                $this->line('    表列表:');
                foreach (array_keys($tables) as $tableName) {
                    $this->line("      - {$tableName}");
                }
            }
        }
    }

    /**
     * 估算记录数量（基于INSERT语句数量）
     */
    private function estimateRecordCount(string $sqlContent): int
    {
        // 简单估算：计算VALUES后面的行数
        preg_match_all('/INSERT INTO.*?VALUES\s*(.*?);/s', $sqlContent, $matches);

        $totalRecords = 0;
        foreach ($matches[1] as $values) {
            // 计算逗号分隔的值组数量
            $totalRecords += substr_count($values, '(');
        }

        return $totalRecords;
    }

    /**
     * 格式化字节大小
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= 1 << 10 * $pow;

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
