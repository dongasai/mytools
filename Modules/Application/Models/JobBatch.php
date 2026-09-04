<?php

namespace Modules\Application\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 批量队列任务模型
 *
 * field start
 *
 * @property string $id
 * @property string $name
 * @property int $total_jobs
 * @property int $pending_jobs
 * @property int $failed_jobs
 * @property array $failed_job_ids
 * @property array $options
 * @property int $cancelled_at
 * @property int $created_at
 * @property int $finished_at
 *                            field end
 */
class JobBatch extends Model
{
    protected $table = 'job_batches';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $casts = [
        'total_jobs' => 'integer',
        'pending_jobs' => 'integer',
        'failed_jobs' => 'integer',
        'failed_job_ids' => 'array',
        'options' => 'array',
        'cancelled_at' => 'integer',
        'created_at' => 'integer',
        'finished_at' => 'integer',
    ];

    protected $appends = [
        'batch_name',
        'created_at_formatted',
        'finished_at_formatted',
        'cancelled_at_formatted',
        'status',
        'progress_percentage',
        'success_jobs',
        'failure_rate',
        'runtime_seconds',
        'runtime_formatted',
    ];

    // attrlist start
    protected $fillable = [
        'id',
        'name',
        'total_jobs',
        'pending_jobs',
        'failed_jobs',
        'failed_job_ids',
        'options',
        'cancelled_at',
        'finished_at',
    ];
    // attrlist end

    /**
     * 获取批次名称访问器
     */
    public function getBatchNameAttribute(): string
    {
        return $this->name ?? '未命名批次';
    }

    /**
     * 获取格式化的创建时间
     */
    public function getCreatedAtFormattedAttribute(): string
    {
        return $this->created_at ? date('Y-m-d H:i:s', $this->created_at) : '';
    }

    /**
     * 获取格式化的完成时间
     */
    public function getFinishedAtFormattedAttribute(): string
    {
        return $this->finished_at ? date('Y-m-d H:i:s', $this->finished_at) : '';
    }

    /**
     * 获取格式化的取消时间
     */
    public function getCancelledAtFormattedAttribute(): string
    {
        return $this->cancelled_at ? date('Y-m-d H:i:s', $this->cancelled_at) : '';
    }

    /**
     * 获取批次状态
     */
    public function getStatusAttribute(): string
    {
        if ($this->cancelled_at) {
            return '已取消';
        }

        if ($this->finished_at) {
            return '已完成';
        }

        if ($this->pending_jobs > 0) {
            return '处理中';
        }

        return '未知';
    }

    /**
     * 获取完成进度百分比
     */
    public function getProgressPercentageAttribute(): float
    {
        if ($this->total_jobs == 0) {
            return 0;
        }

        $completed = $this->total_jobs - $this->pending_jobs;

        return round(($completed / $this->total_jobs) * 100, 2);
    }

    /**
     * 获取成功任务数
     */
    public function getSuccessJobsAttribute(): int
    {
        return $this->total_jobs - $this->pending_jobs - $this->failed_jobs;
    }

    /**
     * 获取失败率
     */
    public function getFailureRateAttribute(): float
    {
        if ($this->total_jobs == 0) {
            return 0;
        }

        return round(($this->failed_jobs / $this->total_jobs) * 100, 2);
    }

    /**
     * 获取运行时长（秒）
     */
    public function getRuntimeSecondsAttribute(): ?int
    {
        if (! $this->created_at) {
            return null;
        }

        $endTime = $this->finished_at ?? $this->cancelled_at ?? time();

        return $endTime - $this->created_at;
    }

    /**
     * 获取格式化的运行时长
     */
    public function getRuntimeFormattedAttribute(): string
    {
        $seconds = $this->runtime_seconds;
        if ($seconds === null) {
            return '';
        }

        if ($seconds < 60) {
            return $seconds.'秒';
        } elseif ($seconds < 3600) {
            return round($seconds / 60, 1).'分钟';
        } else {
            return round($seconds / 3600, 1).'小时';
        }
    }
}
