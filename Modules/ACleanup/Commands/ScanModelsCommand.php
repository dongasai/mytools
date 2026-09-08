<?php

namespace Modules\AClean\Commands;

use Modules\AClean\Logics\ModelScannerLogic;
use Illuminate\Console\Command;

/**
 * 扫描Model类命令
 *
 * 用于扫描系统中的Model类并生成清理配置
 */
class ScanModelsCommand extends Command
{
    /**
     * 命令签名
     */
    protected $signature = 'aclean:scan-models 
                            {--force : 强制刷新所有Model配置}
                            {--show-details : 显示详细信息}';

    /**
     * 命令描述
     */
    protected $description = '扫描系统中的Model类并生成清理配置';

    /**
     * 执行命令
     */
    public function handle(): int
    {
        $this->info('开始扫描Model类...');
        $this->newLine();

        $forceRefresh = $this->option('force');
        $showDetails = $this->option('show-details');

        if ($forceRefresh) {
            $this->warn('强制刷新模式：将重新生成所有Model的配置');
        }

        try {
            // 执行扫描
            $result = ModelScannerLogic::scanAllModels($forceRefresh);

            // 显示扫描结果
            $this->displayScanResults($result, $showDetails);

            $this->newLine();
            $this->info('✅ Model类扫描完成！');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ 扫描失败: '.$e->getMessage());
            $this->error('详细错误: '.$e->getTraceAsString());

            return Command::FAILURE;
        }
    }

    /**
     * 显示扫描结果
     */
    private function displayScanResults(array $result, bool $showDetails): void
    {
        // 显示统计信息
        $this->table(
            ['项目', '数量'],
            [
                ['总Model数', $result['total_models']],
                ['已扫描', $result['scanned_models']],
                ['新增配置', $result['new_models']],
                ['更新配置', $result['updated_models']],
                ['扫描耗时', $result['scan_time'].'s'],
            ]
        );

        if ($showDetails && ! empty($result['models'])) {
            $this->newLine();
            $this->info('📋 详细信息:');

            $tableData = [];
            foreach ($result['models'] as $model) {
                $status = $model['is_new'] ? '新增' : ($model['is_updated'] ? '更新' : '跳过');
                $tableData[] = [
                    $model['model_class'],
                    $model['table_name'],
                    $model['module_name'],
                    $model['data_category']->getDescription(),
                    $status,
                ];
            }

            $this->table(
                ['Model类', '表名', '模块', '数据分类', '状态'],
                $tableData
            );
        }

        // 显示模块统计
        if (! empty($result['models'])) {
            $moduleStats = [];
            foreach ($result['models'] as $model) {
                $module = $model['module_name'];
                if (! isset($moduleStats[$module])) {
                    $moduleStats[$module] = 0;
                }
                $moduleStats[$module]++;
            }

            $this->newLine();
            $this->info('📊 模块统计:');

            $moduleTableData = [];
            foreach ($moduleStats as $module => $count) {
                $moduleTableData[] = [$module, $count];
            }

            $this->table(['模块名', 'Model数量'], $moduleTableData);
        }
    }
}
