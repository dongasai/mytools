<?php

namespace Modules\AClean\Commands;

use Modules\AClean\Enums\CLEANUP_TYPE;
use Modules\AClean\Logics\CleanupExecutorLogic;
use Modules\AClean\Models\CleanupPlanContent;
use Illuminate\Console\Command;

/**
 * 测试基于Model的清理功能
 */
class TestModelCleanupCommand extends Command
{
    /**
     * 命令签名
     */
    protected $signature = 'aclean:test-model 
                            {model_class : Model类名}
                            {--type=3 : 清理类型(1=TRUNCATE,2=DELETE_ALL,3=DELETE_BY_TIME)}
                            {--dry-run : 预览模式，不实际执行}';

    /**
     * 命令描述
     */
    protected $description = '测试基于Model类的清理功能';

    /**
     * 执行命令
     */
    public function handle(): int
    {
        $modelClass = $this->argument('model_class');
        $cleanupType = (int) $this->option('type');
        $dryRun = $this->option('dry-run');

        $this->info('测试Model清理功能');
        $this->info("Model类: {$modelClass}");
        $this->info('清理类型: '.CLEANUP_TYPE::from($cleanupType)->getDescription());
        $this->info('模式: '.($dryRun ? '预览模式' : '实际执行'));
        $this->newLine();

        try {
            // 检查Model类是否存在
            if (! class_exists($modelClass)) {
                $this->error("Model类不存在: {$modelClass}");

                return Command::FAILURE;
            }

            $model = new $modelClass;
            $tableName = $model->getTable();
            $currentCount = $modelClass::count();

            $this->info("表名: {$tableName}");
            $this->info("当前记录数: {$currentCount}");
            $this->newLine();

            // 创建测试的计划内容
            $content = new CleanupPlanContent;
            $content->model_class = $modelClass;
            $content->table_name = $tableName;
            $content->cleanup_type = $cleanupType;
            $content->batch_size = 100;
            $content->is_enabled = true;

            // 设置清理条件
            switch ($cleanupType) {
                case CLEANUP_TYPE::DELETE_BY_TIME->value:
                    $content->conditions = [
                        'time_field' => 'created_at',
                        'before' => '30_days_ago',
                    ];
                    break;
                case CLEANUP_TYPE::DELETE_BY_USER->value:
                    $content->conditions = [
                        'user_field' => 'user_id',
                        'user_condition' => 'in',
                        'user_values' => [999999], // 不存在的用户ID
                    ];
                    break;
                default:
                    $content->conditions = [];
            }

            if ($dryRun) {
                $this->info('🔍 预览模式 - 计算将要删除的记录数...');

                // 这里可以添加预览逻辑
                $affectedRecords = $this->calculateAffectedRecords($modelClass, $cleanupType, $content->conditions);

                $this->table(
                    ['项目', '数值'],
                    [
                        ['当前记录数', $currentCount],
                        ['将删除记录数', $affectedRecords],
                        ['剩余记录数', $currentCount - $affectedRecords],
                    ]
                );
            } else {
                $this->warn('⚠️  即将执行实际清理操作！');
                if (! $this->confirm('确定要继续吗？')) {
                    $this->info('操作已取消');

                    return Command::SUCCESS;
                }

                $this->info('🚀 开始执行清理...');

                // 使用反射调用私有方法进行测试
                $reflection = new \ReflectionClass(CleanupExecutorLogic::class);
                $method = $reflection->getMethod('executeModelCleanup');
                $method->setAccessible(true);

                $startTime = microtime(true);
                $result = $method->invoke(null, $content, 0, $startTime);

                if ($result['success']) {
                    $this->info('✅ 清理成功！');
                    $this->table(
                        ['项目', '数值'],
                        [
                            ['删除记录数', $result['deleted_records']],
                            ['执行时间', $result['execution_time'].'s'],
                            ['当前记录数', $modelClass::count()],
                        ]
                    );
                } else {
                    $this->error('❌ 清理失败: '.$result['message']);
                }
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ 执行失败: '.$e->getMessage());
            $this->error('详细错误: '.$e->getTraceAsString());

            return Command::FAILURE;
        }
    }

    /**
     * 计算受影响的记录数
     */
    private function calculateAffectedRecords(string $modelClass, int $cleanupType, array $conditions): int
    {
        switch ($cleanupType) {
            case CLEANUP_TYPE::TRUNCATE->value:
            case CLEANUP_TYPE::DELETE_ALL->value:
                return $modelClass::count();

            case CLEANUP_TYPE::DELETE_BY_TIME->value:
                if (empty($conditions['time_field']) || empty($conditions['before'])) {
                    return 0;
                }
                $timeField = $conditions['time_field'];
                $beforeTime = $this->parseTimeCondition($conditions['before']);

                return $modelClass::where($timeField, '<', $beforeTime)->count();

            case CLEANUP_TYPE::DELETE_BY_USER->value:
                if (empty($conditions['user_field']) || empty($conditions['user_values'])) {
                    return 0;
                }
                $userField = $conditions['user_field'];
                $userCondition = $conditions['user_condition'] ?? 'in';
                $userValues = $conditions['user_values'];

                $query = $modelClass::query();
                switch ($userCondition) {
                    case 'in':
                        $query->whereIn($userField, $userValues);
                        break;
                    case 'not_in':
                        $query->whereNotIn($userField, $userValues);
                        break;
                    case 'null':
                        $query->whereNull($userField);
                        break;
                    case 'not_null':
                        $query->whereNotNull($userField);
                        break;
                }

                return $query->count();

            default:
                return 0;
        }
    }

    /**
     * 解析时间条件
     */
    private function parseTimeCondition(string $timeCondition): string
    {
        if (preg_match('/(\d+)_days?_ago/', $timeCondition, $matches)) {
            return now()->subDays((int) $matches[1])->toDateTimeString();
        }

        if (preg_match('/(\d+)_months?_ago/', $timeCondition, $matches)) {
            return now()->subMonths((int) $matches[1])->toDateTimeString();
        }

        if (preg_match('/(\d+)_years?_ago/', $timeCondition, $matches)) {
            return now()->subYears((int) $matches[1])->toDateTimeString();
        }

        return $timeCondition;
    }
}
