<?php

namespace Modules\AClean\Models;

use Modules\AClean\Enums\TASK_STATUS;
use Modules\AClean\Helpers\FormatHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 清理任务模型
 *
 * 存储清理任务的执行信息和状态，即执行某个计划的具体实例
 * field start
 *
 * @property int $id 主键ID
 * @property string $task_name 任务名称
 * @property int $plan_id 关联的清理计划ID
 * @property int $backup_id 关联的备份ID
 * @property int $status 任务状态:1待执行,2备份中,3执行中,4已完成,5已失败,6已取消,7已暂停
 * @property float $progress 执行进度百分比
 * @property string $current_step 当前执行步骤
 * @property int $total_tables 总表数
 * @property int $processed_tables 已处理表数
 * @property int $total_records 总记录数
 * @property int $deleted_records 已删除记录数
 * @property int $backup_size 备份文件大小(字节)
 * @property float $execution_time 执行时间(秒)
 * @property float $backup_time 备份时间(秒)
 * @property \Carbon\Carbon $started_at 开始时间
 * @property \Carbon\Carbon $backup_completed_at 备份完成时间
 * @property \Carbon\Carbon $completed_at 完成时间
 * @property string $error_message 错误信息
 * @property int $created_by 创建者用户ID
 * @property \Carbon\Carbon $created_at 创建时间
 * @property \Carbon\Carbon $updated_at 更新时间
 *                                      field end
 */
class CleanupTask extends Model
{
    /**
     * 数据表名
     */
    protected $table = 'cleanup_tasks';

    /**
     * 字段类型转换
     */
    protected $casts = [
        'plan_id' => 'integer',
        'backup_id' => 'integer',
        'status' => 'integer',
        'progress' => 'decimal:2',
        'total_tables' => 'integer',
        'processed_tables' => 'integer',
        'total_records' => 'integer',
        'deleted_records' => 'integer',
        'backup_size' => 'integer',
        'execution_time' => 'decimal:3',
        'backup_time' => 'decimal:3',
        'created_by' => 'integer',
        'started_at' => 'datetime',
        'backup_completed_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 获取任务状态枚举
     */
    public function getStatusEnumAttribute(): TASK_STATUS
    {
        return TASK_STATUS::from($this->status);
    }

    /**
     * 获取任务状态描述
     */
    public function getStatusNameAttribute(): string
    {
        return $this->getStatusEnumAttribute()->getDescription();
    }

    /**
     * 获取任务状态颜色
     */
    public function getStatusColorAttribute(): string
    {
        return $this->getStatusEnumAttribute()->getColor();
    }

    /**
     * 获取任务状态图标
     */
    public function getStatusIconAttribute(): string
    {
        return $this->getStatusEnumAttribute()->getIcon();
    }

    /**
     * 判断任务是否正在运行
     */
    public function getIsRunningAttribute(): bool
    {
        return $this->getStatusEnumAttribute()->isRunning();
    }

    /**
     * 判断任务是否已完成
     */
    public function getIsFinishedAttribute(): bool
    {
        return $this->getStatusEnumAttribute()->isFinished();
    }

    /**
     * 判断任务是否可以执行
     */
    public function getCanExecuteAttribute(): bool
    {
        return $this->getStatusEnumAttribute()->canExecute();
    }

    /**
     * 判断任务是否可以取消
     */
    public function getCanCancelAttribute(): bool
    {
        return $this->getStatusEnumAttribute()->canCancel();
    }

    /**
     * 判断任务是否可以暂停
     */
    public function getCanPauseAttribute(): bool
    {
        return $this->getStatusEnumAttribute()->canPause();
    }

    /**
     * 判断任务是否可以恢复
     */
    public function getCanResumeAttribute(): bool
    {
        return $this->getStatusEnumAttribute()->canResume();
    }

    /**
     * 获取进度百分比文本
     */
    public function getProgressTextAttribute(): string
    {
        return number_format($this->progress, 2).'%';
    }

    /**
     * 获取进度条颜色
     */
    public function getProgressColorAttribute(): string
    {
        if ($this->progress >= 100) {
            return 'success';
        } elseif ($this->progress >= 50) {
            return 'primary';
        } elseif ($this->progress >= 25) {
            return 'warning';
        } else {
            return 'info';
        }
    }

    /**
     * 获取格式化的备份大小
     */
    public function getBackupSizeFormattedAttribute(): string
    {
        return FormatHelper::formatBytes($this->backup_size);
    }

    /**
     * 获取格式化的执行时间
     */
    public function getExecutionTimeFormattedAttribute(): string
    {
        return FormatHelper::formatExecutionTime($this->execution_time);
    }

    /**
     * 获取格式化的备份时间
     */
    public function getBackupTimeFormattedAttribute(): string
    {
        return FormatHelper::formatExecutionTime($this->backup_time);
    }

    /**
     * 获取总执行时间
     */
    public function getTotalTimeAttribute(): float
    {
        return $this->execution_time + $this->backup_time;
    }

    /**
     * 获取格式化的总执行时间
     */
    public function getTotalTimeFormattedAttribute(): string
    {
        return FormatHelper::formatExecutionTime($this->total_time);
    }

    /**
     * 获取删除记录的百分比
     */
    public function getDeletedPercentageAttribute(): float
    {
        if ($this->total_records == 0) {
            return 0;
        }

        return ($this->deleted_records / $this->total_records) * 100;
    }

    /**
     * 获取处理表的百分比
     */
    public function getProcessedPercentageAttribute(): float
    {
        if ($this->total_tables == 0) {
            return 0;
        }

        return ($this->processed_tables / $this->total_tables) * 100;
    }

    /**
     * 获取任务持续时间
     */
    public function getDurationAttribute(): ?float
    {
        if (! $this->started_at) {
            return null;
        }

        $endTime = $this->completed_at ?? now();

        return $this->started_at->diffInSeconds($endTime);
    }

    /**
     * 获取格式化的任务持续时间
     */
    public function getDurationFormattedAttribute(): ?string
    {
        $duration = $this->duration;

        return $duration ? FormatHelper::formatExecutionTime($duration) : null;
    }

    /**
     * 关联清理计划
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(CleanupPlan::class, 'plan_id');
    }

    /**
     * 关联备份记录
     */
    public function backup(): BelongsTo
    {
        return $this->belongsTo(CleanupBackup::class, 'backup_id');
    }

    /**
     * 关联清理日志
     */
    public function logs(): HasMany
    {
        return $this->hasMany(CleanupLog::class, 'task_id');
    }

    /**
     * 作用域：按计划筛选
     */
    public function scopeByPlan($query, int $planId)
    {
        return $query->where('plan_id', $planId);
    }

    /**
     * 作用域：按状态筛选
     */
    public function scopeByStatus($query, int $status)
    {
        return $query->where('status', $status);
    }

    /**
     * 作用域：正在运行的任务
     */
    public function scopeRunning($query)
    {
        return $query->whereIn('status', TASK_STATUS::getRunningStatuses());
    }

    /**
     * 作用域：已完成的任务
     */
    public function scopeFinished($query)
    {
        return $query->whereIn('status', TASK_STATUS::getFinishedStatuses());
    }

    /**
     * 作用域：按创建者筛选
     */
    public function scopeByCreator($query, int $createdBy)
    {
        return $query->where('created_by', $createdBy);
    }

    /**
     * 作用域：按任务名称搜索
     */
    public function scopeSearchName($query, string $search)
    {
        return $query->where('task_name', 'like', "%{$search}%");
    }

    /**
     * 作用域：最近的任务
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * 获取任务状态统计
     */
    public static function getStatusStats(): array
    {
        $stats = static::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->keyBy('status')
            ->toArray();

        $result = [];
        foreach (TASK_STATUS::cases() as $status) {
            $result[$status->value] = [
                'name' => $status->getDescription(),
                'count' => $stats[$status->value]['count'] ?? 0,
                'color' => $status->getColor(),
                'icon' => $status->getIcon(),
            ];
        }

        return $result;
    }

    /**
     * 获取最近任务统计
     */
    public static function getRecentStats(int $days = 7): array
    {
        $query = static::where('created_at', '>=', now()->subDays($days));

        return [
            'total' => $query->count(),
            'completed' => $query->clone()->where('status', TASK_STATUS::COMPLETED->value)->count(),
            'failed' => $query->clone()->where('status', TASK_STATUS::FAILED->value)->count(),
            'running' => $query->clone()->whereIn('status', TASK_STATUS::getRunningStatuses())->count(),
        ];
    }
}
