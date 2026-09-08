<?php

namespace Modules\AClean\Commands;

use Modules\AClean\Enums\PLAN_TYPE;
use Modules\AClean\Models\CleanupPlan;
use Modules\AClean\Models\CleanupTask;
use Modules\AClean\Services\ACleanService;
use Illuminate\Console\Command;

/**
 * 数据清理命令
 *
 * 提供完整的数据清理命令行接口
 */
class CleanupDataCommand extends Command
{
    /**
     * 命令签名
     */
    protected $signature = 'aclean:data 
                            {action : 操作类型: create-plan|show-plan|create-task|execute|status|preview}
                            {id? : 计划ID或任务ID}
                            {--name= : 计划或任务名称}
                            {--type= : 计划类型: 1=全量,2=模块,3=分类,4=自定义,5=混合}
                            {--modules= : 目标模块列表(逗号分隔)}
                            {--categories= : 目标分类列表(逗号分隔)}
                            {--tables= : 目标表列表(逗号分隔)}
                            {--exclude-tables= : 排除表列表(逗号分隔)}
                            {--exclude-modules= : 排除模块列表(逗号分隔)}
                            {--execute : 创建任务后立即执行}
                            {--dry-run : 预演模式，不实际执行}
                            {--force : 强制执行，跳过确认}';

    /**
     * 命令描述
     */
    protected $description = '数据清理管理命令';

    /**
     * 执行命令
     */
    public function handle(): int
    {
        $action = $this->argument('action');

        try {
            switch ($action) {
                case 'create-plan':
                    return $this->createPlan();
                case 'show-plan':
                    return $this->showPlan();
                case 'create-task':
                    return $this->createTask();
                case 'execute':
                    return $this->executeTask();
                case 'status':
                    return $this->showStatus();
                case 'preview':
                    return $this->previewCleanup();
                default:
                    $this->error("未知的操作类型: {$action}");
                    $this->showHelp();

                    return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error('❌ 操作失败: '.$e->getMessage());
            if ($this->getOutput()->isVerbose()) {
                $this->error($e->getTraceAsString());
            }

            return Command::FAILURE;
        }
    }

    /**
     * 创建清理计划
     */
    private function createPlan(): int
    {
        $this->info('📋 创建清理计划');
        $this->newLine();

        // 获取计划参数
        $planName = $this->option('name') ?: $this->ask('请输入计划名称');
        $planType = $this->option('type') ?: $this->choice(
            '请选择计划类型',
            PLAN_TYPE::getOptions(),
            PLAN_TYPE::CUSTOM->value
        );

        $planData = [
            'plan_name' => $planName,
            'plan_type' => (int) $planType,
            'description' => '通过命令行创建的清理计划',
        ];

        // 根据计划类型配置目标选择
        $targetSelection = $this->buildTargetSelection((int) $planType);
        if ($targetSelection) {
            $planData['target_selection'] = $targetSelection;
        }

        // 创建计划
        $result = ACleanService::createCleanupPlan($planData);

        if ($result['success']) {
            $this->info('✅ 清理计划创建成功！');
            $this->info("计划ID: {$result['plan_id']}");
            $this->info("计划名称: {$planName}");

            // 生成计划内容
            $this->info('正在生成计划内容...');
            $contentResult = ACleanService::generatePlanContents($result['plan_id']);

            if ($contentResult['success']) {
                $this->info("✅ 计划内容生成成功！包含 {$contentResult['total_tables']} 个表");
            } else {
                $this->warn('⚠️ 计划内容生成失败: '.$contentResult['message']);
            }
        } else {
            $this->error('❌ 计划创建失败: '.$result['message']);

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * 显示计划详情
     */
    private function showPlan(): int
    {
        $planId = $this->argument('id');
        if (! $planId) {
            $this->error('请提供计划ID');

            return Command::FAILURE;
        }

        $plan = CleanupPlan::with(['contents', 'tasks'])->find($planId);
        if (! $plan) {
            $this->error("计划 {$planId} 不存在");

            return Command::FAILURE;
        }

        $this->info("📋 计划详情 (ID: {$planId})");
        $this->newLine();

        // 基本信息
        $this->table(
            ['属性', '值'],
            [
                ['计划名称', $plan->plan_name],
                ['计划类型', $plan->plan_type_name],
                ['状态', $plan->enabled_text],
                ['创建时间', $plan->created_at->format('Y-m-d H:i:s')],
                ['内容数量', $plan->contents_count],
                ['任务数量', $plan->tasks_count],
            ]
        );

        // 目标选择
        if ($plan->target_selection) {
            $this->newLine();
            $this->info('🎯 目标选择:');
            $this->line(json_encode($plan->target_selection, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        // 计划内容
        if ($plan->contents->isNotEmpty()) {
            $this->newLine();
            $this->info('📝 计划内容:');

            $contentData = [];
            foreach ($plan->contents as $content) {
                $contentData[] = [
                    $content->table_name,
                    $content->cleanup_type_name,
                    $content->priority,
                    $content->enabled_text,
                    $content->backup_text,
                ];
            }

            $this->table(
                ['表名', '清理类型', '优先级', '状态', '备份'],
                $contentData
            );
        }

        return Command::SUCCESS;
    }

    /**
     * 创建清理任务
     */
    private function createTask(): int
    {
        $planId = $this->argument('id');
        if (! $planId) {
            $this->error('请提供计划ID');

            return Command::FAILURE;
        }

        $plan = CleanupPlan::find($planId);
        if (! $plan) {
            $this->error("计划 {$planId} 不存在");

            return Command::FAILURE;
        }

        $this->info("🚀 基于计划 '{$plan->plan_name}' 创建清理任务");
        $this->newLine();

        $taskOptions = [
            'task_name' => $this->option('name') ?: $plan->plan_name.'_'.date('Ymd_His'),
            'execute_immediately' => $this->option('execute'),
        ];

        $result = ACleanService::createCleanupTask($planId, $taskOptions);

        if ($result['success']) {
            $this->info('✅ 清理任务创建成功！');
            $this->info("任务ID: {$result['task_id']}");
            $this->info("任务名称: {$taskOptions['task_name']}");

            if ($this->option('execute')) {
                $this->info('正在执行清理任务...');

                return $this->executeTaskById($result['task_id']);
            }
        } else {
            $this->error('❌ 任务创建失败: '.$result['message']);

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * 执行清理任务
     */
    private function executeTask(): int
    {
        $taskId = $this->argument('id');
        if (! $taskId) {
            $this->error('请提供任务ID');

            return Command::FAILURE;
        }

        return $this->executeTaskById($taskId);
    }

    /**
     * 执行指定的清理任务
     */
    private function executeTaskById(int $taskId): int
    {
        $task = CleanupTask::find($taskId);
        if (! $task) {
            $this->error("任务 {$taskId} 不存在");

            return Command::FAILURE;
        }

        $this->info("🚀 执行清理任务: {$task->task_name}");
        $this->newLine();

        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        if ($dryRun) {
            $this->warn('⚠️ 预演模式：不会实际执行清理操作');
        }

        if (! $force && ! $dryRun) {
            if (! $this->confirm('确认要执行此清理任务吗？此操作不可撤销！')) {
                $this->info('操作已取消');

                return Command::SUCCESS;
            }
        }

        $result = ACleanService::executeCleanupTask($taskId, $dryRun);

        if ($result['success']) {
            $this->info('✅ 任务执行成功！');
            $this->displayExecutionResult($result);
        } else {
            $this->error('❌ 任务执行失败: '.$result['message']);

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * 显示任务状态
     */
    private function showStatus(): int
    {
        $taskId = $this->argument('id');

        if ($taskId) {
            return $this->showTaskStatus($taskId);
        } else {
            return $this->showOverallStatus();
        }
    }

    /**
     * 显示特定任务状态
     */
    private function showTaskStatus(int $taskId): int
    {
        $task = CleanupTask::with('plan')->find($taskId);
        if (! $task) {
            $this->error("任务 {$taskId} 不存在");

            return Command::FAILURE;
        }

        $this->info("📊 任务状态 (ID: {$taskId})");
        $this->newLine();

        $this->table(
            ['属性', '值'],
            [
                ['任务名称', $task->task_name],
                ['关联计划', $task->plan->plan_name],
                ['状态', $task->status_name],
                ['进度', $task->progress_text],
                ['当前步骤', $task->current_step ?: '-'],
                ['总表数', $task->total_tables],
                ['已处理表数', $task->processed_tables],
                ['总记录数', number_format($task->total_records)],
                ['已删除记录数', number_format($task->deleted_records)],
                ['执行时间', $task->execution_time_formatted],
                ['开始时间', $task->started_at ? $task->started_at->format('Y-m-d H:i:s') : '-'],
                ['完成时间', $task->completed_at ? $task->completed_at->format('Y-m-d H:i:s') : '-'],
            ]
        );

        if ($task->error_message) {
            $this->newLine();
            $this->error('错误信息: '.$task->error_message);
        }

        return Command::SUCCESS;
    }

    /**
     * 显示整体状态
     */
    private function showOverallStatus(): int
    {
        $this->info('📊 系统整体状态');
        $this->newLine();

        $stats = ACleanService::getCleanupStats();
        $health = ACleanService::getSystemHealth();

        // 显示健康状态
        $healthColor = match ($health['health']) {
            'good' => 'green',
            'warning' => 'yellow',
            'critical' => 'red',
            default => 'white',
        };

        $this->line("<fg={$healthColor}>系统健康状态: ".strtoupper($health['health']).'</>');

        if (! empty($health['issues'])) {
            $this->newLine();
            $this->warn('⚠️ 发现的问题:');
            foreach ($health['issues'] as $issue) {
                $this->line("• {$issue}");
            }
        }

        // 显示统计信息
        $this->newLine();
        $this->info('📈 统计信息:');

        $this->table(
            ['类型', '总数', '详细信息'],
            [
                ['计划', $stats['plans']['total'], "启用: {$stats['plans']['enabled']}, 禁用: {$stats['plans']['disabled']}"],
                ['任务', array_sum(array_column($stats['tasks'], 'count')), $this->formatTaskStats($stats['tasks'])],
                ['备份', array_sum(array_column($stats['backups'], 'count')), $this->formatBackupStats($stats['backups'])],
            ]
        );

        return Command::SUCCESS;
    }

    /**
     * 预览清理结果
     */
    private function previewCleanup(): int
    {
        $id = $this->argument('id');
        if (! $id) {
            $this->error('请提供计划ID或任务ID');

            return Command::FAILURE;
        }

        // 尝试作为计划ID
        $plan = CleanupPlan::find($id);
        if ($plan) {
            $result = ACleanService::previewPlanCleanup($id);
            $this->info("📋 计划预览: {$plan->plan_name}");
        } else {
            // 尝试作为任务ID
            $task = CleanupTask::find($id);
            if ($task) {
                $result = ACleanService::previewTaskCleanup($id);
                $this->info("🚀 任务预览: {$task->task_name}");
            } else {
                $this->error("计划或任务 {$id} 不存在");

                return Command::FAILURE;
            }
        }

        $this->newLine();
        $this->displayPreviewResult($result);

        return Command::SUCCESS;
    }

    /**
     * 构建目标选择配置
     */
    private function buildTargetSelection(int $planType): ?array
    {
        $planTypeEnum = PLAN_TYPE::from($planType);

        if (! $planTypeEnum->needsTargetSelection()) {
            return null;
        }

        $selection = [];

        switch ($planTypeEnum) {
            case PLAN_TYPE::MODULE:
                $modules = $this->option('modules');
                if ($modules) {
                    $selection = [
                        'selection_type' => 'module',
                        'modules' => explode(',', $modules),
                    ];
                }
                break;

            case PLAN_TYPE::CATEGORY:
                $categories = $this->option('categories');
                if ($categories) {
                    $selection = [
                        'selection_type' => 'category',
                        'categories' => array_map('intval', explode(',', $categories)),
                    ];
                }
                break;

            case PLAN_TYPE::CUSTOM:
                $tables = $this->option('tables');
                if ($tables) {
                    $selection = [
                        'selection_type' => 'custom',
                        'tables' => explode(',', $tables),
                    ];
                }
                break;

            case PLAN_TYPE::MIXED:
                $selection = ['selection_type' => 'mixed'];

                if ($modules = $this->option('modules')) {
                    $selection['modules'] = explode(',', $modules);
                }
                if ($categories = $this->option('categories')) {
                    $selection['categories'] = array_map('intval', explode(',', $categories));
                }
                if ($tables = $this->option('tables')) {
                    $selection['tables'] = explode(',', $tables);
                }
                break;
        }

        // 添加排除选项
        if ($excludeTables = $this->option('exclude-tables')) {
            $selection['exclude_tables'] = explode(',', $excludeTables);
        }
        if ($excludeModules = $this->option('exclude-modules')) {
            $selection['exclude_modules'] = explode(',', $excludeModules);
        }

        return empty($selection) ? null : $selection;
    }

    /**
     * 显示执行结果
     */
    private function displayExecutionResult(array $result): void
    {
        $this->table(
            ['项目', '值'],
            [
                ['处理表数', $result['processed_tables'] ?? 0],
                ['删除记录数', number_format($result['deleted_records'] ?? 0)],
                ['执行时间', $result['execution_time'] ?? '0 秒'],
                ['备份大小', $result['backup_size'] ?? '0 B'],
            ]
        );
    }

    /**
     * 显示预览结果
     */
    private function displayPreviewResult(array $result): void
    {
        $this->table(
            ['项目', '值'],
            [
                ['总表数', $result['total_tables'] ?? 0],
                ['总记录数', number_format($result['total_records'] ?? 0)],
                ['预计删除记录数', number_format($result['estimated_deleted_records'] ?? 0)],
                ['预计释放空间', $result['estimated_size_mb'] ?? '0 MB'],
                ['预计执行时间', $result['estimated_time_seconds'] ?? '0 秒'],
            ]
        );

        if (! empty($result['tables_preview'])) {
            $this->newLine();
            $this->info('📋 表详细预览:');

            $tableData = [];
            foreach ($result['tables_preview'] as $table) {
                $tableData[] = [
                    $table['table_name'],
                    number_format($table['current_records']),
                    number_format($table['records_to_delete']),
                    $table['size_to_free_mb'].' MB',
                    $table['cleanup_type'],
                ];
            }

            $this->table(
                ['表名', '当前记录数', '将删除记录数', '释放空间', '清理类型'],
                $tableData
            );
        }
    }

    /**
     * 格式化任务统计
     */
    private function formatTaskStats(array $stats): string
    {
        $parts = [];
        foreach ($stats as $status => $info) {
            if ($info['count'] > 0) {
                $parts[] = "{$info['name']}: {$info['count']}";
            }
        }

        return implode(', ', $parts);
    }

    /**
     * 格式化备份统计
     */
    private function formatBackupStats(array $stats): string
    {
        $parts = [];
        foreach ($stats as $status => $info) {
            if ($info['count'] > 0) {
                $parts[] = "{$info['name']}: {$info['count']}";
            }
        }

        return implode(', ', $parts);
    }

    /**
     * 显示帮助信息
     */
    private function showHelp(): void
    {
        $this->newLine();
        $this->info('📖 使用示例:');
        $this->line('');
        $this->line('# 创建清理计划');
        $this->line('php artisan aclean:data create-plan --name="农场模块清理" --type=2 --modules=Farm');
        $this->line('');
        $this->line('# 查看计划详情');
        $this->line('php artisan aclean:data show-plan 1');
        $this->line('');
        $this->line('# 创建并执行任务');
        $this->line('php artisan aclean:data create-task 1 --execute');
        $this->line('');
        $this->line('# 预览清理结果');
        $this->line('php artisan aclean:data preview 1');
        $this->line('');
        $this->line('# 查看任务状态');
        $this->line('php artisan aclean:data status 1');
    }
}
