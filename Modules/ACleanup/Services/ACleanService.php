<?php

namespace Modules\AClean\Services;

use Modules\AClean\Enums\TASK_STATUS;
use Modules\AClean\Logics\CleanupExecutorLogic;
use Modules\AClean\Logics\CleanupPlanLogic;
use Modules\AClean\Logics\CleanupTaskLogic;
use Modules\AClean\Logics\ModelScannerLogic;
use Modules\AClean\Models\CleanupPlan;
use Modules\AClean\Models\CleanupTask;

/**
 * 数据清理服务类
 *
 * 提供对外的清理服务接口，包括清理计划的统一管理
 */
class ACleanService
{
    /**
     * 扫描Model类（推荐使用）
     *
     * @param  bool  $forceRefresh  是否强制刷新
     * @return array 扫描结果
     */
    public static function scanModels(bool $forceRefresh = false): array
    {
        return ModelScannerLogic::scanAllModels($forceRefresh);
    }

    /**
     * 扫描系统中的所有数据表（已废弃，建议使用scanModels）
     *
     * @deprecated 请使用 scanModels() 方法
     *
     * @param  bool  $forceRefresh  是否强制刷新
     * @return array 扫描结果
     */
    public static function scanTables(bool $forceRefresh = false): array
    {
        // 为了向后兼容，重定向到新的Model扫描方法
        return static::scanModels($forceRefresh);
    }

    /**
     * 创建清理计划
     *
     * @param  array  $planData  计划数据
     * @return array 创建结果
     */
    public static function createCleanupPlan(array $planData): array
    {
        try {
            $plan = CleanupPlanLogic::createPlan($planData);

            return [
                'success' => true,
                'message' => '清理计划创建成功',
                'data' => [
                    'plan_id' => $plan->id,
                    'plan_name' => $plan->plan_name,
                    'plan_type' => $plan->plan_type,
                    'contents_count' => $plan->contents()->count(),
                ],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '创建清理计划失败: '.$e->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * 为计划生成内容配置
     *
     * @param  int  $planId  计划ID
     * @param  bool  $autoGenerate  是否自动生成
     * @return array 生成结果
     */
    public static function generatePlanContents(int $planId, bool $autoGenerate = true): array
    {
        try {
            $result = CleanupPlanLogic::generateContents($planId, $autoGenerate);

            return [
                'success' => empty($result['errors']),
                'message' => "内容生成完成，生成 {$result['generated_count']} 个，跳过 {$result['skipped_count']} 个",
                'data' => $result,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '生成计划内容失败: '.$e->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * 获取计划详情
     *
     * @param  int  $planId  计划ID
     * @return array 计划详情
     */
    public static function getPlanDetails(int $planId): array
    {
        try {
            $result = CleanupPlanLogic::getPlanDetails($planId);

            return [
                'success' => true,
                'data' => $result,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '获取计划详情失败: '.$e->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * 更新计划
     *
     * @param  int  $planId  计划ID
     * @param  array  $planData  计划数据
     * @return array 更新结果
     */
    public static function updateCleanupPlan(int $planId, array $planData): array
    {
        try {
            $plan = CleanupPlanLogic::updatePlan($planId, $planData);

            return [
                'success' => true,
                'message' => '计划更新成功',
                'data' => [
                    'plan_id' => $plan->id,
                    'plan_name' => $plan->plan_name,
                ],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '更新计划失败: '.$e->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * 删除计划
     *
     * @param  int  $planId  计划ID
     * @return array 删除结果
     */
    public static function deleteCleanupPlan(int $planId): array
    {
        try {
            CleanupPlanLogic::deletePlan($planId);

            return [
                'success' => true,
                'message' => '计划删除成功',
                'data' => null,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '删除计划失败: '.$e->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * 基于计划创建清理任务
     *
     * @param  int  $planId  计划ID
     * @param  array  $taskOptions  任务选项
     * @return array 创建结果
     */
    public static function createCleanupTask(int $planId, array $taskOptions = []): array
    {
        try {
            $task = CleanupTaskLogic::createTask($planId, $taskOptions);

            return [
                'success' => true,
                'message' => '清理任务创建成功',
                'data' => [
                    'task_id' => $task->id,
                    'task_name' => $task->task_name,
                    'status' => TASK_STATUS::from($task->status)->getDescription(),
                    'total_tables' => $task->total_tables,
                ],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '创建清理任务失败: '.$e->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * 获取任务详情
     *
     * @param  int  $taskId  任务ID
     * @return array 任务详情
     */
    public static function getTaskDetails(int $taskId): array
    {
        try {
            $result = CleanupTaskLogic::getTaskDetails($taskId);

            return [
                'success' => true,
                'data' => $result,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '获取任务详情失败: '.$e->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * 取消任务
     *
     * @param  int  $taskId  任务ID
     * @param  string  $reason  取消原因
     * @return array 取消结果
     */
    public static function cancelTask(int $taskId, string $reason = ''): array
    {
        try {
            $task = CleanupTaskLogic::cancelTask($taskId, $reason);

            return [
                'success' => true,
                'message' => '任务已取消',
                'data' => [
                    'task_id' => $task->id,
                    'status' => TASK_STATUS::from($task->status)->getDescription(),
                ],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '取消任务失败: '.$e->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * 启动任务执行
     *
     * @param  int  $taskId  任务ID
     * @return array 启动结果
     */
    public static function startTask(int $taskId): array
    {
        try {
            $task = CleanupTaskLogic::startTask($taskId);

            return [
                'success' => true,
                'message' => '任务启动成功',
                'data' => [
                    'task_id' => $task->id,
                    'status' => TASK_STATUS::from($task->status)->getDescription(),
                ],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '启动任务失败: '.$e->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * 暂停任务执行
     *
     * @param  int  $taskId  任务ID
     * @return array 暂停结果
     */
    public static function pauseTask(int $taskId): array
    {
        try {
            $task = CleanupTaskLogic::pauseTask($taskId);

            return [
                'success' => true,
                'message' => '任务暂停成功',
                'data' => [
                    'task_id' => $task->id,
                    'status' => TASK_STATUS::from($task->status)->getDescription(),
                ],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '暂停任务失败: '.$e->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * 恢复任务执行
     *
     * @param  int  $taskId  任务ID
     * @return array 恢复结果
     */
    public static function resumeTask(int $taskId): array
    {
        try {
            $task = CleanupTaskLogic::resumeTask($taskId);

            return [
                'success' => true,
                'message' => '任务恢复成功',
                'data' => [
                    'task_id' => $task->id,
                    'status' => TASK_STATUS::from($task->status)->getDescription(),
                ],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '恢复任务失败: '.$e->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * 停止任务执行
     *
     * @param  int  $taskId  任务ID
     * @return array 停止结果
     */
    public static function stopTask(int $taskId): array
    {
        try {
            $task = CleanupTaskLogic::stopTask($taskId);

            return [
                'success' => true,
                'message' => '任务停止成功',
                'data' => [
                    'task_id' => $task->id,
                    'status' => TASK_STATUS::from($task->status)->getDescription(),
                ],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '停止任务失败: '.$e->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * 获取任务执行进度
     *
     * @param  int  $taskId  任务ID
     * @return array 进度信息
     */
    public static function getTaskProgress(int $taskId): array
    {
        try {
            $result = CleanupTaskLogic::getTaskProgress($taskId);

            return [
                'success' => true,
                'data' => $result,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '获取任务进度失败: '.$e->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * 预览计划的清理结果
     *
     * @param  int  $planId  计划ID
     * @return array 预览结果
     */
    public static function previewPlanCleanup(int $planId): array
    {
        try {
            $result = CleanupExecutorLogic::previewPlanCleanup($planId);

            return [
                'success' => true,
                'data' => $result,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '预览计划清理失败: '.$e->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * 预览任务的清理结果
     *
     * @param  int  $taskId  任务ID
     * @return array 预览结果
     */
    public static function previewTaskCleanup(int $taskId): array
    {
        try {
            $result = CleanupExecutorLogic::previewTaskCleanup($taskId);

            return [
                'success' => true,
                'data' => $result,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '预览任务清理失败: '.$e->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * 执行清理任务
     *
     * @param  int  $taskId  任务ID
     * @param  bool  $dryRun  是否为预演模式
     * @return array 执行结果
     */
    public static function executeCleanupTask(int $taskId, bool $dryRun = false): array
    {
        try {
            $result = CleanupExecutorLogic::executeTask($taskId, $dryRun);

            return [
                'success' => $result['success'] ?? empty($result['errors'] ?? []),
                'message' => empty($result['errors'] ?? []) ? '清理任务执行成功' : '清理任务执行完成，但有错误',
                'data' => $result,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '执行清理任务失败: '.$e->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * 清理历史日志
     *
     * @param  int  $retentionDays  保留天数
     * @return array 清理结果
     */
    public static function cleanHistoryLogs(int $retentionDays = 30): array
    {
        try {
            $deletedCount = CleanupTaskLogic::cleanHistoryLogs($retentionDays);

            return [
                'success' => true,
                'message' => "清理完成，删除 {$deletedCount} 条历史日志",
                'data' => [
                    'deleted_count' => $deletedCount,
                    'retention_days' => $retentionDays,
                ],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '清理历史日志失败: '.$e->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * 获取系统健康状态
     *
     * @return array 健康状态
     */
    public static function getSystemHealth(): array
    {
        $runningTasks = CleanupTask::running()->count();
        $failedTasks = CleanupTask::byStatus(TASK_STATUS::FAILED->value)
            ->where('created_at', '>=', now()->subDay())
            ->count();

        $health = 'good';
        $issues = [];

        if ($runningTasks > 5) {
            $health = 'warning';
            $issues[] = "有 {$runningTasks} 个任务正在运行，可能存在性能问题";
        }

        if ($failedTasks > 0) {
            $health = 'warning';
            $issues[] = "最近24小时内有 {$failedTasks} 个任务执行失败";
        }

        if ($failedTasks > 5) {
            $health = 'critical';
        }

        return [
            'health' => $health,
            'issues' => $issues,
            'metrics' => [
                'running_tasks' => $runningTasks,
                'failed_tasks_24h' => $failedTasks,
            ],
        ];
    }

    /**
     * 获取推荐的清理计划
     *
     * @return array 推荐计划
     */
    public static function getRecommendedPlans(): array
    {
        $recommendations = [];

        // 检查是否有大量日志数据
        $logTables = \Modules\AClean\Models\CleanupConfig::byCategory(
            \Modules\AClean\Enums\DATA_CATEGORY::LOG_DATA->value
        )->get();

        if ($logTables->count() > 0) {
            $recommendations[] = [
                'type' => 'category',
                'title' => '日志数据清理',
                'description' => '清理系统中的日志数据，释放存储空间',
                'config' => [
                    'plan_type' => \Modules\AClean\Enums\PLAN_TYPE::CATEGORY->value,
                    'target_selection' => [
                        'selection_type' => 'category',
                        'categories' => [\Modules\AClean\Enums\DATA_CATEGORY::LOG_DATA->value],
                    ],
                ],
                'estimated_tables' => $logTables->count(),
            ];
        }

        // 检查是否有缓存数据
        $cacheTables = \Modules\AClean\Models\CleanupConfig::byCategory(
            \Modules\AClean\Enums\DATA_CATEGORY::CACHE_DATA->value
        )->get();

        if ($cacheTables->count() > 0) {
            $recommendations[] = [
                'type' => 'category',
                'title' => '缓存数据清理',
                'description' => '清理系统中的缓存数据，提高系统性能',
                'config' => [
                    'plan_type' => \Modules\AClean\Enums\PLAN_TYPE::CATEGORY->value,
                    'target_selection' => [
                        'selection_type' => 'category',
                        'categories' => [\Modules\AClean\Enums\DATA_CATEGORY::CACHE_DATA->value],
                    ],
                ],
                'estimated_tables' => $cacheTables->count(),
            ];
        }

        return $recommendations;
    }
}