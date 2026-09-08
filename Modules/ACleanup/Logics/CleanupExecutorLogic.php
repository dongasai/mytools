<?php

namespace Modules\AClean\Logics;

use Modules\AClean\Enums\CLEANUP_TYPE;
use Modules\AClean\Enums\TASK_STATUS;
use Modules\AClean\Models\CleanupLog;
use Modules\AClean\Models\CleanupPlan;
use Modules\AClean\Models\CleanupPlanContent;
use Modules\AClean\Models\CleanupTask;
use Modules\Application\Services\SystemLogService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * 清理执行逻辑类
 *
 * 负责实际的数据清理执行操作
 */
class CleanupExecutorLogic
{
    /**
     * 预览计划的清理结果
     *
     * @param  int  $planId  计划ID
     * @return array 预览数据（plan + summary + tables）
     */
    public static function previewPlanCleanup(int $planId): array
    {
        $plan = CleanupPlan::with('contents')->findOrFail($planId);

        $previewData = [];
        $totalRecords = 0;
        $totalTables = 0;

        foreach ($plan->contents->where('is_enabled', true) as $content) {
            $tablePreview = static::previewTableCleanup($content);
            $previewData[] = $tablePreview;
            $totalRecords += $tablePreview['affected_records'];
            $totalTables++;
        }

        return [
            'plan' => [
                'id' => $plan->id,
                'plan_name' => $plan->plan_name,
                'description' => $plan->description,
            ],
            'summary' => [
                'total_tables' => $totalTables,
                'total_records' => $totalRecords,
                'estimated_time' => static::estimateExecutionTime($totalRecords),
            ],
            'tables' => $previewData,
        ];
    }

    /**
     * 预览任务的清理结果
     *
     * @param  int  $taskId  任务ID
     * @return array 预览数据
     *
     * @throws \Exception 如果任务关联的计划不存在
     */
    public static function previewTaskCleanup(int $taskId): array
    {
        $task = CleanupTask::with('plan.contents')->findOrFail($taskId);

        if (! $task->plan) {
            throw new \Exception('任务关联的计划不存在');
        }

        return static::previewPlanCleanup($task->plan->id);
    }

    /**
     * 执行清理任务
     *
     * @param  int  $taskId  任务ID
     * @param  bool  $dryRun  是否为预演模式
     * @return array 执行结果数据
     *
     * @throws \Exception 如果任务状态不正确
     */
    public static function executeTask(int $taskId, bool $dryRun = false): array
    {
        $task = CleanupTask::with('plan.contents')->findOrFail($taskId);

        if (! $task->plan) {
            throw new \Exception('任务关联的计划不存在');
        }

        // 检查任务状态
        $currentStatus = TASK_STATUS::from($task->status);
        if ($currentStatus !== TASK_STATUS::PENDING) {
            throw new \Exception('任务状态不正确，无法执行');
        }

        if ($dryRun) {
            return static::previewTaskCleanup($taskId);
        }

        // 开始执行任务
        return static::executeTaskInternal($task);
    }

    /**
     * 内部执行任务逻辑
     *
     * 保留外层 try-catch：需要在 catch 中更新任务失败状态后 rethrow
     *
     * @param  CleanupTask  $task  任务对象
     * @return array 执行结果数据
     */
    private static function executeTaskInternal(CleanupTask $task): array
    {
        $startTime = microtime(true);
        $totalDeleted = 0;
        $processedTables = 0;
        $errors = [];

        try {
            // 更新任务状态为执行中
            CleanupTaskLogic::updateTaskStatus($task->id, TASK_STATUS::RUNNING, [
                'current_step' => '开始执行清理',
            ]);

            // 获取启用的内容配置
            $enabledContents = $task->plan->contents->where('is_enabled', true)->sortBy('priority');

            foreach ($enabledContents as $content) {
                try {
                    // 更新当前步骤
                    $targetName = $content->model_class ?: $content->table_name;
                    CleanupTaskLogic::updateTaskProgress(
                        $task->id,
                        $processedTables,
                        $totalDeleted,
                        "正在清理: {$targetName}"
                    );

                    // 执行清理（支持Model类和表名两种模式）
                    $result = static::executeCleanup($content, $task->id);

                    if ($result['success']) {
                        $totalDeleted += $result['deleted_records'];
                        $processedTables++;
                    } else {
                        $targetName = $content->model_class ?: $content->table_name;
                        $errors[] = "{$targetName}: ".$result['message'];
                    }
                } catch (\Exception $e) {
                    $targetName = $content->model_class ?: $content->table_name;
                    $errors[] = "{$targetName}: ".$e->getMessage();
                    Log::error('清理失败', [
                        'task_id' => $task->id,
                        'model_class' => $content->model_class,
                        'table_name' => $content->table_name,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $executionTime = round(microtime(true) - $startTime, 3);

            // 更新任务完成状态
            $finalStatus = empty($errors) ? TASK_STATUS::COMPLETED : TASK_STATUS::FAILED;
            CleanupTaskLogic::updateTaskStatus($task->id, $finalStatus, [
                'total_records' => $totalDeleted,
                'deleted_records' => $totalDeleted,
                'execution_time' => $executionTime,
                'error_message' => empty($errors) ? null : implode('; ', $errors),
                'current_step' => $finalStatus === TASK_STATUS::COMPLETED ? '清理完成' : '清理失败',
            ]);

            return [
                'task_id' => $task->id,
                'processed_tables' => $processedTables,
                'total_tables' => $enabledContents->count(),
                'deleted_records' => $totalDeleted,
                'execution_time' => $executionTime,
                'errors' => $errors,
                'success' => $finalStatus === TASK_STATUS::COMPLETED,
            ];
        } catch (\Exception $e) {
            // 更新任务失败状态
            CleanupTaskLogic::updateTaskStatus($task->id, TASK_STATUS::FAILED, [
                'error_message' => $e->getMessage(),
                'execution_time' => round(microtime(true) - $startTime, 3),
                'current_step' => '执行失败',
            ]);

            // 记录系统日志
            SystemLogService::exception('cleanup', $e, [
                'task_id' => $task->id,
                'action' => 'execute_task',
                'processed_tables' => $processedTables,
                'total_deleted' => $totalDeleted,
            ]);

            throw $e;
        }
    }

    /**
     * 预览表清理
     *
     * 保留 try-catch：表可能不存在，需要优雅处理
     *
     * @param  CleanupPlanContent  $content  计划内容
     * @return array 预览结果
     */
    private static function previewTableCleanup(CleanupPlanContent $content): array
    {
        try {
            $tableName = $content->table_name;
            $cleanupType = CLEANUP_TYPE::from($content->cleanup_type);

            // 检查表是否存在
            if (! Schema::hasTable($tableName)) {
                return [
                    'table_name' => $tableName,
                    'cleanup_type' => $cleanupType->getDescription(),
                    'affected_records' => 0,
                    'current_records' => 0,
                    'error' => '表不存在',
                ];
            }

            $currentRecords = DB::table($tableName)->count();
            $affectedRecords = static::calculateAffectedRecords($tableName, $cleanupType, $content->conditions);

            return [
                'table_name' => $tableName,
                'cleanup_type' => $cleanupType->getDescription(),
                'affected_records' => $affectedRecords,
                'current_records' => $currentRecords,
                'remaining_records' => $currentRecords - $affectedRecords,
                'conditions' => $content->conditions,
                'batch_size' => $content->batch_size,
                'backup_enabled' => $content->backup_enabled,
            ];
        } catch (\Exception $e) {
            return [
                'table_name' => $content->table_name,
                'cleanup_type' => CLEANUP_TYPE::from($content->cleanup_type)->getDescription(),
                'affected_records' => 0,
                'current_records' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * 计算受影响的记录数
     *
     * @param  string  $tableName  表名
     * @param  CLEANUP_TYPE  $cleanupType  清理类型
     * @param  array  $conditions  清理条件
     * @return int 受影响的记录数
     */
    private static function calculateAffectedRecords(string $tableName, CLEANUP_TYPE $cleanupType, array $conditions): int
    {
        switch ($cleanupType) {
            case CLEANUP_TYPE::TRUNCATE:
            case CLEANUP_TYPE::DELETE_ALL:
                return DB::table($tableName)->count();

            case CLEANUP_TYPE::DELETE_BY_TIME:
                return static::countByTimeCondition($tableName, $conditions);

            case CLEANUP_TYPE::DELETE_BY_USER:
                return static::countByUserCondition($tableName, $conditions);

            case CLEANUP_TYPE::DELETE_BY_CONDITION:
                return static::countByCustomCondition($tableName, $conditions);

            default:
                return 0;
        }
    }

    /**
     * 按时间条件统计记录数
     *
     * @param  string  $tableName  表名
     * @param  array  $conditions  条件
     * @return int 记录数
     */
    private static function countByTimeCondition(string $tableName, array $conditions): int
    {
        $query = DB::table($tableName);

        if (! empty($conditions['time_field']) && ! empty($conditions['before'])) {
            $timeField = $conditions['time_field'];
            $beforeTime = static::parseTimeCondition($conditions['before']);
            $query->where($timeField, '<', $beforeTime);
        }

        return $query->count();
    }

    /**
     * 按用户条件统计记录数
     *
     * @param  string  $tableName  表名
     * @param  array  $conditions  条件
     * @return int 记录数
     */
    private static function countByUserCondition(string $tableName, array $conditions): int
    {
        $query = DB::table($tableName);

        if (! empty($conditions['user_field']) && ! empty($conditions['user_ids'])) {
            $userField = $conditions['user_field'];
            $userIds = is_array($conditions['user_ids']) ? $conditions['user_ids'] : [$conditions['user_ids']];
            $query->whereIn($userField, $userIds);
        }

        return $query->count();
    }

    /**
     * 按自定义条件统计记录数
     *
     * @param  string  $tableName  表名
     * @param  array  $conditions  条件
     * @return int 记录数
     */
    private static function countByCustomCondition(string $tableName, array $conditions): int
    {
        $query = DB::table($tableName);

        if (! empty($conditions['where'])) {
            foreach ($conditions['where'] as $condition) {
                if (isset($condition['field'], $condition['operator'], $condition['value'])) {
                    $query->where($condition['field'], $condition['operator'], $condition['value']);
                }
            }
        }

        return $query->count();
    }

    /**
     * 检查Model是否支持软删除
     *
     * @param  Model  $model  Model实例
     * @return bool 是否支持软删除
     */
    private static function modelSupportsSoftDeletes(Model $model): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive($model));
    }

    /**
     * 解析时间条件
     *
     * @param  string  $timeCondition  时间条件
     * @return string 解析后的时间
     */
    private static function parseTimeCondition(string $timeCondition): string
    {
        // 支持格式：30_days_ago, 1_month_ago, 2024-01-01, 等
        if (preg_match('/(\d+)_days?_ago/', $timeCondition, $matches)) {
            return now()->subDays((int) $matches[1])->toDateTimeString();
        }

        if (preg_match('/(\d+)_months?_ago/', $timeCondition, $matches)) {
            return now()->subMonths((int) $matches[1])->toDateTimeString();
        }

        if (preg_match('/(\d+)_years?_ago/', $timeCondition, $matches)) {
            return now()->subYears((int) $matches[1])->toDateTimeString();
        }

        // 直接返回时间字符串
        return $timeCondition;
    }

    /**
     * 估算执行时间
     *
     * @param  int  $totalRecords  总记录数
     * @return string 估算时间
     */
    private static function estimateExecutionTime(int $totalRecords): string
    {
        // 简单估算：每秒处理1000条记录
        $seconds = ceil($totalRecords / 1000);

        if ($seconds < 60) {
            return "{$seconds}秒";
        } elseif ($seconds < 3600) {
            $minutes = ceil($seconds / 60);

            return "{$minutes}分钟";
        } else {
            $hours = ceil($seconds / 3600);

            return "{$hours}小时";
        }
    }

    /**
     * 执行清理（支持Model类和表名两种模式）
     *
     * 保留 try-catch：单个表清理失败收集错误，不中断整个任务
     *
     * @param  CleanupPlanContent  $content  计划内容
     * @param  int  $taskId  任务ID
     * @return array 执行结果
     */
    private static function executeCleanup(CleanupPlanContent $content, int $taskId): array
    {
        $startTime = microtime(true);
        $cleanupType = CLEANUP_TYPE::from($content->cleanup_type);

        try {
            // 优先使用Model类，如果没有则使用表名
            if (! empty($content->model_class)) {
                return static::executeModelCleanup($content, $taskId, $startTime);
            } else {
                return static::executeTableCleanup($content, $taskId, $startTime);
            }
        } catch (\Exception $e) {
            $executionTime = round(microtime(true) - $startTime, 3);

            // 记录错误日志
            static::logCleanupOperation($taskId, $content, $cleanupType, [
                'error' => $e->getMessage(),
                'execution_time' => $executionTime,
                'conditions' => $content->conditions,
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'deleted_records' => 0,
                'execution_time' => $executionTime,
            ];
        }
    }

    /**
     * 执行基于Model类的清理
     *
     * @param  CleanupPlanContent  $content  计划内容
     * @param  int  $taskId  任务ID
     * @param  float  $startTime  开始时间
     * @return array 执行结果
     */
    private static function executeModelCleanup(CleanupPlanContent $content, int $taskId, float $startTime): array
    {
        $modelClass = $content->model_class;
        $cleanupType = CLEANUP_TYPE::from($content->cleanup_type);

        // 检查Model类是否存在
        if (! class_exists($modelClass)) {
            throw new \Exception("Model类不存在: {$modelClass}");
        }

        $model = new $modelClass;

        // 记录清理前的记录数
        $beforeCount = $modelClass::count();

        // 执行清理
        static::performModelCleanup($modelClass, $cleanupType, $content->conditions, $content->batch_size);

        // 记录清理后的记录数
        $afterCount = $modelClass::count();
        $actualDeleted = $beforeCount - $afterCount;

        $executionTime = round(microtime(true) - $startTime, 3);

        // 记录清理日志
        static::logCleanupOperation($taskId, $content, $cleanupType, [
            'before_count' => $beforeCount,
            'after_count' => $afterCount,
            'deleted_records' => $actualDeleted,
            'execution_time' => $executionTime,
            'conditions' => $content->conditions,
            'batch_size' => $content->batch_size,
        ]);

        return [
            'success' => true,
            'message' => "Model {$modelClass} 清理成功",
            'deleted_records' => $actualDeleted,
            'execution_time' => $executionTime,
        ];
    }

    /**
     * 执行基于表名的清理（向后兼容）
     *
     * @param  CleanupPlanContent  $content  计划内容
     * @param  int  $taskId  任务ID
     * @param  float  $startTime  开始时间
     * @return array 执行结果
     */
    private static function executeTableCleanup(CleanupPlanContent $content, int $taskId, float $startTime): array
    {
        $tableName = $content->table_name;
        $cleanupType = CLEANUP_TYPE::from($content->cleanup_type);

        // 检查表是否存在
        if (! Schema::hasTable($tableName)) {
            throw new \Exception("表 {$tableName} 不存在");
        }

        // 记录清理前的记录数
        $beforeCount = DB::table($tableName)->count();

        // 执行清理
        static::performTableCleanup($tableName, $cleanupType, $content->conditions, $content->batch_size);

        // 记录清理后的记录数
        $afterCount = DB::table($tableName)->count();
        $actualDeleted = $beforeCount - $afterCount;

        $executionTime = round(microtime(true) - $startTime, 3);

        // 记录清理日志
        static::logCleanupOperation($taskId, $content, $cleanupType, [
            'before_count' => $beforeCount,
            'after_count' => $afterCount,
            'deleted_records' => $actualDeleted,
            'execution_time' => $executionTime,
            'conditions' => $content->conditions,
            'batch_size' => $content->batch_size,
        ]);

        return [
            'success' => true,
            'message' => "表 {$tableName} 清理成功",
            'deleted_records' => $actualDeleted,
            'execution_time' => $executionTime,
        ];
    }

    /**
     * 执行基于Model类的清理操作
     *
     * @param  string  $modelClass  Model类名
     * @param  CLEANUP_TYPE  $cleanupType  清理类型
     * @param  array  $conditions  清理条件
     * @param  int  $batchSize  批处理大小
     * @return int 删除的记录数
     */
    private static function performModelCleanup(string $modelClass, CLEANUP_TYPE $cleanupType, array $conditions, int $batchSize): int
    {
        switch ($cleanupType) {
            case CLEANUP_TYPE::TRUNCATE:
                return static::performModelTruncate($modelClass);

            case CLEANUP_TYPE::DELETE_ALL:
                return static::performModelDeleteAll($modelClass, $batchSize);

            case CLEANUP_TYPE::DELETE_BY_TIME:
                return static::performModelDeleteByTime($modelClass, $conditions, $batchSize);

            case CLEANUP_TYPE::DELETE_BY_USER:
                return static::performModelDeleteByUser($modelClass, $conditions, $batchSize);

            case CLEANUP_TYPE::DELETE_BY_CONDITION:
                return static::performModelDeleteByCondition($modelClass, $conditions, $batchSize);

            default:
                throw new \Exception("不支持的清理类型: {$cleanupType->value}");
        }
    }

    /**
     * 执行基于表名的清理操作（向后兼容）
     *
     * @param  string  $tableName  表名
     * @param  CLEANUP_TYPE  $cleanupType  清理类型
     * @param  array  $conditions  清理条件
     * @param  int  $batchSize  批处理大小
     * @return int 删除的记录数
     */
    private static function performTableCleanup(string $tableName, CLEANUP_TYPE $cleanupType, array $conditions, int $batchSize): int
    {
        switch ($cleanupType) {
            case CLEANUP_TYPE::TRUNCATE:
                return static::performTableTruncate($tableName);

            case CLEANUP_TYPE::DELETE_ALL:
                return static::performTableDeleteAll($tableName, $batchSize);

            case CLEANUP_TYPE::DELETE_BY_TIME:
                return static::performTableDeleteByTime($tableName, $conditions, $batchSize);

            case CLEANUP_TYPE::DELETE_BY_USER:
                return static::performTableDeleteByUser($tableName, $conditions, $batchSize);

            case CLEANUP_TYPE::DELETE_BY_CONDITION:
                return static::performTableDeleteByCondition($tableName, $conditions, $batchSize);

            default:
                throw new \Exception("不支持的清理类型: {$cleanupType->value}");
        }
    }

    /**
     * 执行Model的TRUNCATE操作
     *
     * @param  string  $modelClass  Model类名
     * @return int 删除的记录数
     */
    private static function performModelTruncate(string $modelClass): int
    {
        $beforeCount = $modelClass::count();
        $modelClass::truncate();

        return $beforeCount;
    }

    /**
     * 执行表的TRUNCATE操作（向后兼容）
     *
     * @param  string  $tableName  表名
     * @return int 删除的记录数
     */
    private static function performTableTruncate(string $tableName): int
    {
        $beforeCount = DB::table($tableName)->count();
        DB::statement("TRUNCATE TABLE `{$tableName}`");

        return $beforeCount;
    }

    /**
     * 执行Model的DELETE ALL操作
     *
     * @param  string  $modelClass  Model类名
     * @param  int  $batchSize  批处理大小
     * @return int 删除的记录数
     */
    private static function performModelDeleteAll(string $modelClass, int $batchSize): int
    {
        $totalDeleted = 0;

        do {
            $deleted = $modelClass::limit($batchSize)->delete();
            $totalDeleted += $deleted;
        } while ($deleted > 0);

        return $totalDeleted;
    }

    /**
     * 执行表的DELETE ALL操作（向后兼容）
     *
     * @param  string  $tableName  表名
     * @param  int  $batchSize  批处理大小
     * @return int 删除的记录数
     */
    private static function performTableDeleteAll(string $tableName, int $batchSize): int
    {
        $totalDeleted = 0;

        do {
            $deleted = DB::table($tableName)->limit($batchSize)->delete();
            $totalDeleted += $deleted;
        } while ($deleted > 0);

        return $totalDeleted;
    }

    /**
     * 执行Model的按时间删除操作
     *
     * @param  string  $modelClass  Model类名
     * @param  array  $conditions  条件
     * @param  int  $batchSize  批处理大小
     * @return int 删除的记录数
     */
    private static function performModelDeleteByTime(string $modelClass, array $conditions, int $batchSize): int
    {
        if (empty($conditions['time_field']) || empty($conditions['before'])) {
            throw new \Exception('时间删除条件不完整');
        }

        $timeField = $conditions['time_field'];
        $beforeTime = static::parseTimeCondition($conditions['before']);
        $totalDeleted = 0;

        // 检查Model是否支持软删除
        $model = new $modelClass;
        $supportsSoftDeletes = static::modelSupportsSoftDeletes($model);
        $forceDelete = $conditions['force_delete'] ?? false;

        do {
            $query = $modelClass::where($timeField, '<', $beforeTime)->limit($batchSize);

            if ($supportsSoftDeletes && $forceDelete) {
                $deleted = $query->forceDelete();
            } else {
                $deleted = $query->delete();
            }

            $totalDeleted += $deleted;
        } while ($deleted > 0);

        return $totalDeleted;
    }

    /**
     * 执行表的按时间删除操作（向后兼容）
     *
     * @param  string  $tableName  表名
     * @param  array  $conditions  条件
     * @param  int  $batchSize  批处理大小
     * @return int 删除的记录数
     */
    private static function performTableDeleteByTime(string $tableName, array $conditions, int $batchSize): int
    {
        if (empty($conditions['time_field']) || empty($conditions['before'])) {
            throw new \Exception('时间删除条件不完整');
        }

        $timeField = $conditions['time_field'];
        $beforeTime = static::parseTimeCondition($conditions['before']);
        $totalDeleted = 0;

        do {
            $deleted = DB::table($tableName)
                ->where($timeField, '<', $beforeTime)
                ->limit($batchSize)
                ->delete();
            $totalDeleted += $deleted;
        } while ($deleted > 0);

        return $totalDeleted;
    }

    /**
     * 执行Model的按用户删除操作
     *
     * @param  string  $modelClass  Model类名
     * @param  array  $conditions  条件
     * @param  int  $batchSize  批处理大小
     * @return int 删除的记录数
     */
    private static function performModelDeleteByUser(string $modelClass, array $conditions, int $batchSize): int
    {
        if (empty($conditions['user_field']) || empty($conditions['user_values'])) {
            throw new \Exception('用户删除条件不完整');
        }

        $userField = $conditions['user_field'];
        $userCondition = $conditions['user_condition'] ?? 'in';
        $userValues = is_array($conditions['user_values']) ? $conditions['user_values'] : [$conditions['user_values']];
        $totalDeleted = 0;

        // 检查Model是否支持软删除
        $model = new $modelClass;
        $supportsSoftDeletes = static::modelSupportsSoftDeletes($model);
        $forceDelete = $conditions['force_delete'] ?? false;

        do {
            $query = $modelClass::query();

            // 应用用户条件
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
                default:
                    throw new \Exception("不支持的用户条件: {$userCondition}");
            }

            $query->limit($batchSize);

            if ($supportsSoftDeletes && $forceDelete) {
                $deleted = $query->forceDelete();
            } else {
                $deleted = $query->delete();
            }

            $totalDeleted += $deleted;
        } while ($deleted > 0);

        return $totalDeleted;
    }

    /**
     * 执行表的按用户删除操作（向后兼容）
     *
     * @param  string  $tableName  表名
     * @param  array  $conditions  条件
     * @param  int  $batchSize  批处理大小
     * @return int 删除的记录数
     */
    private static function performTableDeleteByUser(string $tableName, array $conditions, int $batchSize): int
    {
        if (empty($conditions['user_field']) || empty($conditions['user_ids'])) {
            throw new \Exception('用户删除条件不完整');
        }

        $userField = $conditions['user_field'];
        $userIds = is_array($conditions['user_ids']) ? $conditions['user_ids'] : [$conditions['user_ids']];
        $totalDeleted = 0;

        do {
            $deleted = DB::table($tableName)
                ->whereIn($userField, $userIds)
                ->limit($batchSize)
                ->delete();
            $totalDeleted += $deleted;
        } while ($deleted > 0);

        return $totalDeleted;
    }

    /**
     * 执行Model的按条件删除操作
     *
     * @param  string  $modelClass  Model类名
     * @param  array  $conditions  条件
     * @param  int  $batchSize  批处理大小
     * @return int 删除的记录数
     */
    private static function performModelDeleteByCondition(string $modelClass, array $conditions, int $batchSize): int
    {
        if (empty($conditions['where'])) {
            throw new \Exception('自定义删除条件不完整');
        }

        $totalDeleted = 0;

        // 检查Model是否支持软删除
        $model = new $modelClass;
        $supportsSoftDeletes = static::modelSupportsSoftDeletes($model);
        $forceDelete = $conditions['force_delete'] ?? false;

        do {
            $query = $modelClass::query();

            foreach ($conditions['where'] as $condition) {
                if (isset($condition['field'], $condition['operator'], $condition['value'])) {
                    $query->where($condition['field'], $condition['operator'], $condition['value']);
                }
            }

            $query->limit($batchSize);

            if ($supportsSoftDeletes && $forceDelete) {
                $deleted = $query->forceDelete();
            } else {
                $deleted = $query->delete();
            }

            $totalDeleted += $deleted;
        } while ($deleted > 0);

        return $totalDeleted;
    }

    /**
     * 执行表的按条件删除操作（向后兼容）
     *
     * @param  string  $tableName  表名
     * @param  array  $conditions  条件
     * @param  int  $batchSize  批处理大小
     * @return int 删除的记录数
     */
    private static function performTableDeleteByCondition(string $tableName, array $conditions, int $batchSize): int
    {
        if (empty($conditions['where'])) {
            throw new \Exception('自定义删除条件不完整');
        }

        $totalDeleted = 0;

        do {
            $query = DB::table($tableName);

            foreach ($conditions['where'] as $condition) {
                if (isset($condition['field'], $condition['operator'], $condition['value'])) {
                    $query->where($condition['field'], $condition['operator'], $condition['value']);
                }
            }

            $deleted = $query->limit($batchSize)->delete();
            $totalDeleted += $deleted;
        } while ($deleted > 0);

        return $totalDeleted;
    }

    /**
     * 记录清理操作日志
     *
     * 保留 try-catch：日志记录失败不应影响主流程
     *
     * @param  int  $taskId  任务ID
     * @param  CleanupPlanContent  $content  计划内容
     * @param  CLEANUP_TYPE  $cleanupType  清理类型
     * @param  array  $details  详细信息
     */
    private static function logCleanupOperation(int $taskId, CleanupPlanContent $content, CLEANUP_TYPE $cleanupType, array $details): void
    {
        try {
            $tableName = $content->table_name;
            if (! empty($content->model_class)) {
                // 如果有Model类，从Model获取表名
                $modelClass = $content->model_class;
                if (class_exists($modelClass)) {
                    $model = new $modelClass;
                    $tableName = $model->getTable();
                }
            }

            CleanupLog::create([
                'task_id' => $taskId,
                'table_name' => $tableName,
                'cleanup_type' => $cleanupType->value,
                'before_count' => $details['before_count'] ?? 0,
                'after_count' => $details['after_count'] ?? 0,
                'deleted_records' => $details['deleted_records'] ?? 0,
                'execution_time' => $details['execution_time'] ?? 0,
                'conditions' => $details['conditions'] ?? [],
                'error_message' => $details['error'] ?? null,
                'model_class' => $content->model_class,
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('记录清理日志失败', [
                'task_id' => $taskId,
                'model_class' => $content->model_class ?? null,
                'table_name' => $content->table_name ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
