<?php

namespace Modules\AClean\Logics;

use Modules\AClean\Enums\TASK_STATUS;
use Modules\AClean\Models\CleanupLog;
use Modules\AClean\Models\CleanupPlan;
use Modules\AClean\Models\CleanupTask;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 清理任务管理逻辑类
 *
 * 负责清理任务的创建、管理和状态控制
 */
class CleanupTaskLogic
{
    /**
     * 基于计划创建清理任务
     *
     * @param  int  $planId  计划ID
     * @param  array  $taskOptions  任务选项
     * @return CleanupTask 创建的任务 Model
     *
     * @throws \InvalidArgumentException 如果计划状态不允许创建任务
     */
    public static function createTask(int $planId, array $taskOptions = []): CleanupTask
    {
        $validatedOptions = static::validateTaskOptions($taskOptions);

        return DB::transaction(function () use ($planId, $validatedOptions) {
            $plan = CleanupPlan::with('contents')->findOrFail($planId);

            if (! $plan->is_enabled) {
                throw new \InvalidArgumentException('计划已禁用，无法创建任务');
            }

            if ($plan->contents->isEmpty()) {
                throw new \InvalidArgumentException('计划没有配置内容，无法创建任务');
            }

            // 统计任务信息
            $enabledContents = $plan->contents->where('is_enabled', true);
            $totalTables = $enabledContents->count();

            if ($totalTables === 0) {
                throw new \InvalidArgumentException('计划中没有启用的表配置，无法创建任务');
            }

            // 创建任务
            return CleanupTask::create([
                'task_name' => $validatedOptions['task_name'] ?? "清理任务 - {$plan->plan_name}",
                'plan_id' => $planId,
                'status' => TASK_STATUS::PENDING->value,
                'progress' => 0,
                'current_step' => '准备中',
                'total_tables' => $totalTables,
                'processed_tables' => 0,
                'total_records' => 0,
                'deleted_records' => 0,
                'backup_size' => 0,
                'execution_time' => 0,
                'backup_time' => 0,
                'created_by' => $validatedOptions['created_by'] ?? 0,
            ]);
        });
    }

    /**
     * 更新任务状态
     *
     * @param  int  $taskId  任务ID
     * @param  TASK_STATUS  $status  新状态
     * @param  array  $updateData  更新数据
     * @return CleanupTask 更新后的任务 Model
     */
    public static function updateTaskStatus(int $taskId, TASK_STATUS $status, array $updateData = []): CleanupTask
    {
        $task = CleanupTask::findOrFail($taskId);

        $data = array_merge($updateData, [
            'status' => $status->value,
        ]);

        // 根据状态设置时间戳
        switch ($status) {
            case TASK_STATUS::RUNNING:
                $data['started_at'] = now();
                break;
            case TASK_STATUS::BACKING_UP:
                $data['started_at'] = $data['started_at'] ?? now();
                break;
            case TASK_STATUS::COMPLETED:
                $data['completed_at'] = now();
                $data['progress'] = 100;
                break;
            case TASK_STATUS::FAILED:
            case TASK_STATUS::CANCELLED:
                $data['completed_at'] = now();
                break;
        }

        $task->update($data);

        return $task->fresh();
    }

    /**
     * 更新任务进度
     *
     * @param  int  $taskId  任务ID
     * @param  int  $processedTables  已处理表数
     * @param  int  $deletedRecords  已删除记录数
     * @param  string  $currentStep  当前步骤
     * @return CleanupTask 更新后的任务 Model
     */
    public static function updateTaskProgress(
        int $taskId,
        int $processedTables,
        int $deletedRecords,
        string $currentStep
    ): CleanupTask {
        $task = CleanupTask::findOrFail($taskId);

        $progress = $task->total_tables > 0 ? round(($processedTables / $task->total_tables) * 100, 2) : 0;

        $task->update([
            'progress' => $progress,
            'processed_tables' => $processedTables,
            'deleted_records' => $deletedRecords,
            'current_step' => $currentStep,
        ]);

        return $task->fresh();
    }

    /**
     * 取消任务
     *
     * @param  int  $taskId  任务ID
     * @param  string  $reason  取消原因
     * @return CleanupTask 更新后的任务 Model
     *
     * @throws \InvalidArgumentException 如果任务已结束
     */
    public static function cancelTask(int $taskId, string $reason = ''): CleanupTask
    {
        $task = CleanupTask::findOrFail($taskId);

        // 检查任务状态
        $currentStatus = TASK_STATUS::from($task->status);
        if (in_array($currentStatus, [TASK_STATUS::COMPLETED, TASK_STATUS::FAILED, TASK_STATUS::CANCELLED])) {
            throw new \InvalidArgumentException('任务已完成或已取消，无法再次取消');
        }

        $task->update([
            'status' => TASK_STATUS::CANCELLED->value,
            'completed_at' => now(),
            'error_message' => $reason ?: '用户取消',
        ]);

        return $task->fresh();
    }

    /**
     * 获取任务详情
     *
     * @param  int  $taskId  任务ID
     * @return array 任务详情数据（task + plan + backup）
     */
    public static function getTaskDetails(int $taskId): array
    {
        $task = CleanupTask::with(['plan', 'backup'])->findOrFail($taskId);

        return [
            'task' => [
                'id' => $task->id,
                'task_name' => $task->task_name,
                'status' => $task->status,
                'status_name' => TASK_STATUS::from($task->status)->getDescription(),
                'progress' => $task->progress,
                'current_step' => $task->current_step,
                'total_tables' => $task->total_tables,
                'processed_tables' => $task->processed_tables,
                'total_records' => $task->total_records,
                'deleted_records' => $task->deleted_records,
                'backup_size' => $task->backup_size,
                'execution_time' => $task->execution_time,
                'backup_time' => $task->backup_time,
                'started_at' => $task->started_at,
                'backup_completed_at' => $task->backup_completed_at,
                'completed_at' => $task->completed_at,
                'error_message' => $task->error_message,
                'created_at' => $task->created_at,
            ],
            'plan' => $task->plan ? [
                'id' => $task->plan->id,
                'plan_name' => $task->plan->plan_name,
                'plan_type' => $task->plan->plan_type,
                'description' => $task->plan->description,
            ] : null,
            'backup' => $task->backup ? [
                'id' => $task->backup->id,
                'backup_name' => $task->backup->backup_name,
                'backup_size' => $task->backup->backup_size,
                'file_count' => $task->backup->file_count,
                'backup_status' => $task->backup->backup_status,
            ] : null,
        ];
    }

    /**
     * 验证任务选项
     *
     * @param  array  $taskOptions  任务选项
     * @return array 验证后的选项
     */
    private static function validateTaskOptions(array $taskOptions): array
    {
        return [
            'task_name' => $taskOptions['task_name'] ?? null,
            'created_by' => $taskOptions['created_by'] ?? 0,
        ];
    }

    /**
     * 启动任务执行
     *
     * @param  int  $taskId  任务ID
     * @return CleanupTask 更新后的任务 Model
     *
     * @throws \InvalidArgumentException 如果任务状态不允许启动
     */
    public static function startTask(int $taskId): CleanupTask
    {
        $task = CleanupTask::findOrFail($taskId);

        // 检查任务状态
        if ($task->status !== TASK_STATUS::PENDING->value) {
            throw new \InvalidArgumentException('只有待执行状态的任务可以启动');
        }

        // 更新任务状态为执行中
        $task->update([
            'status' => TASK_STATUS::RUNNING->value,
            'started_at' => now(),
            'current_step' => '任务启动中...',
        ]);

        return $task->fresh();
    }

    /**
     * 暂停任务执行
     *
     * @param  int  $taskId  任务ID
     * @return CleanupTask 更新后的任务 Model
     *
     * @throws \InvalidArgumentException 如果任务状态不允许暂停
     */
    public static function pauseTask(int $taskId): CleanupTask
    {
        $task = CleanupTask::findOrFail($taskId);

        // 检查任务状态
        if (! in_array($task->status, [TASK_STATUS::BACKING_UP->value, TASK_STATUS::RUNNING->value])) {
            throw new \InvalidArgumentException('只有备份中或执行中的任务可以暂停');
        }

        // 更新任务状态为已暂停
        $task->update([
            'status' => TASK_STATUS::PAUSED->value,
            'current_step' => '任务已暂停',
        ]);

        return $task->fresh();
    }

    /**
     * 恢复任务执行
     *
     * @param  int  $taskId  任务ID
     * @return CleanupTask 更新后的任务 Model
     *
     * @throws \InvalidArgumentException 如果任务状态不允许恢复
     */
    public static function resumeTask(int $taskId): CleanupTask
    {
        $task = CleanupTask::findOrFail($taskId);

        // 检查任务状态
        if ($task->status !== TASK_STATUS::PAUSED->value) {
            throw new \InvalidArgumentException('只有已暂停的任务可以恢复');
        }

        // 更新任务状态为执行中
        $task->update([
            'status' => TASK_STATUS::RUNNING->value,
            'current_step' => '任务恢复执行中...',
        ]);

        return $task->fresh();
    }

    /**
     * 停止任务执行
     *
     * @param  int  $taskId  任务ID
     * @return CleanupTask 更新后的任务 Model
     *
     * @throws \InvalidArgumentException 如果任务状态不允许停止
     */
    public static function stopTask(int $taskId): CleanupTask
    {
        $task = CleanupTask::findOrFail($taskId);

        // 检查任务状态
        if (! in_array($task->status, [TASK_STATUS::BACKING_UP->value, TASK_STATUS::RUNNING->value, TASK_STATUS::PAUSED->value])) {
            throw new \InvalidArgumentException('只有备份中、执行中或已暂停的任务可以停止');
        }

        // 更新任务状态为已取消
        $task->update([
            'status' => TASK_STATUS::CANCELLED->value,
            'finished_at' => now(),
            'current_step' => '任务已停止',
            'error_message' => '任务被手动停止',
        ]);

        return $task->fresh();
    }

    /**
     * 获取任务进度
     *
     * @param  int  $taskId  任务ID
     * @return array 进度信息数据
     */
    public static function getTaskProgress(int $taskId): array
    {
        $task = CleanupTask::findOrFail($taskId);

        return [
            'task_id' => $task->id,
            'status' => $task->status,
            'progress' => $task->progress,
            'current_step' => $task->current_step,
            'total_tables' => $task->total_tables,
            'processed_tables' => $task->processed_tables,
            'deleted_records' => $task->deleted_records,
            'started_at' => $task->started_at,
            'finished_at' => $task->finished_at,
        ];
    }

    /**
     * 清理历史日志
     *
     * 删除超过指定保留天数的 cleanup_logs 记录
     *
     * @param  int  $retentionDays  保留天数
     * @return int 删除的记录数量
     */
    public static function cleanHistoryLogs(int $retentionDays): int
    {
        $expiredDate = now()->subDays($retentionDays);

        return (int) CleanupLog::where('created_at', '<', $expiredDate)->delete();
    }
}
