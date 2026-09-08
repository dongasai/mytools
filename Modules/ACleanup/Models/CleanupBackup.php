<?php

namespace Modules\AClean\Models;

use Modules\AClean\Enums\BACKUP_STATUS;
use Modules\AClean\Enums\BACKUP_SOURCE_TYPE;
use Modules\AClean\Enums\BACKUP_TYPE;
use Modules\AClean\Enums\COMPRESSION_TYPE;
use Modules\AClean\Helpers\FormatHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 备份记录模型
 *
 * 存储计划的备份方案和备份内容
 * field start
 *
 * @property int $id 主键ID
 * @property int|null $plan_id 关联的计划ID（nullable，source_type区分指向清理计划或独立备份计划）
 * @property int|null $source_type 备份来源类型:1清理计划(CLEANUP_PLAN),2独立备份计划(BACKUP_PLAN),3手动备份(MANUAL)
 * @property int $task_id 关联的清理任务ID(如果是任务触发的备份)
 * @property string $backup_name 备份名称
 * @property int $backup_type 备份类型:1SQL,2JSON,3CSV
 * @property int $compression_type 压缩类型:1none,2gzip,3zip
 * @property string $backup_path 备份文件路径
 * @property int $backup_size 备份文件大小(字节)
 * @property int $original_size 原始数据大小(字节)
 * @property int $tables_count 备份表数量
 * @property int $records_count 备份记录数量
 * @property int $backup_status 备份状态:1进行中,2已完成,3已失败
 * @property string $backup_hash 备份文件MD5哈希
 * @property array $backup_config 备份配置信息
 * @property \Carbon\Carbon $started_at 备份开始时间
 * @property \Carbon\Carbon $completed_at 备份完成时间
 * @property \Carbon\Carbon $expires_at 备份过期时间
 * @property string $error_message 错误信息
 * @property int $created_by 创建者用户ID
 * @property \Carbon\Carbon $created_at 创建时间
 * @property \Carbon\Carbon $updated_at 更新时间
 *                                      field end
 */
class CleanupBackup extends Model
{
    /**
     * 数据表名
     */
    protected $table = 'cleanup_backups';

    /**
     * 可填充字段
     */
    protected $fillable = [
        'backup_name',
        'plan_id',
        'source_type',
        'task_id',
        'backup_type',
        'compression_type',
        'backup_path',
        'backup_size',
        'original_size',
        'tables_count',
        'records_count',
        'total_tables',
        'processed_tables',
        'failed_tables',
        'progress_percent',
        'avg_speed',
        'estimated_remaining',
        'current_table',
        'tables_json',
        'processed_tables_json',
        'backup_status',
        'backup_hash',
        'backup_config',
        'file_count',
        'execution_time',
        'started_at',
        'completed_at',
        'expires_at',
        'error_message',
        'created_by',
    ];

    /**
     * 字段类型转换
     */
    protected $casts = [
        'plan_id' => 'integer',
        'source_type' => 'integer',
        'task_id' => 'integer',
        'backup_type' => 'integer',
        'compression_type' => 'integer',
        'backup_size' => 'integer',
        'original_size' => 'integer',
        'tables_count' => 'integer',
        'records_count' => 'integer',
        'backup_status' => 'integer',
        'backup_config' => 'array',
        'created_by' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 获取备份类型枚举
     */
    public function getBackupTypeEnumAttribute(): BACKUP_TYPE
    {
        return BACKUP_TYPE::from($this->backup_type);
    }

    /**
     * 获取压缩类型枚举
     */
    public function getCompressionTypeEnumAttribute(): COMPRESSION_TYPE
    {
        return COMPRESSION_TYPE::from($this->compression_type);
    }

    /**
     * 获取备份状态枚举
     */
    public function getBackupStatusEnumAttribute(): BACKUP_STATUS
    {
        return BACKUP_STATUS::from($this->backup_status);
    }

    /**
     * 获取备份类型描述
     */
    public function getBackupTypeNameAttribute(): string
    {
        return $this->getBackupTypeEnumAttribute()->getDescription();
    }

    /**
     * 获取压缩类型描述
     */
    public function getCompressionTypeNameAttribute(): string
    {
        return $this->getCompressionTypeEnumAttribute()->getDescription();
    }

    /**
     * 获取备份状态描述
     */
    public function getBackupStatusNameAttribute(): string
    {
        return $this->getBackupStatusEnumAttribute()->getDescription();
    }

    /**
     * 获取备份状态颜色
     */
    public function getBackupStatusColorAttribute(): string
    {
        return $this->getBackupStatusEnumAttribute()->getColor();
    }

    /**
     * 获取备份状态图标
     */
    public function getBackupStatusIconAttribute(): string
    {
        return $this->getBackupStatusEnumAttribute()->getIcon();
    }

    /**
     * 获取格式化的备份大小
     */
    public function getBackupSizeFormattedAttribute(): string
    {
        return FormatHelper::formatBytes($this->backup_size);
    }

    /**
     * 获取格式化的原始大小
     */
    public function getOriginalSizeFormattedAttribute(): string
    {
        return FormatHelper::formatBytes($this->original_size);
    }

    /**
     * 获取压缩率
     */
    public function getCompressionRatioAttribute(): float
    {
        if ($this->original_size == 0) {
            return 0;
        }

        return (1 - $this->backup_size / $this->original_size) * 100;
    }

    /**
     * 获取格式化的压缩率
     */
    public function getCompressionRatioFormattedAttribute(): string
    {
        return number_format($this->compression_ratio, 2).'%';
    }

    /**
     * 获取备份持续时间
     */
    public function getDurationAttribute(): ?float
    {
        if (! $this->started_at || ! $this->completed_at) {
            return null;
        }

        return $this->started_at->diffInSeconds($this->completed_at);
    }

    /**
     * 获取格式化的备份持续时间
     */
    public function getDurationFormattedAttribute(): ?string
    {
        $duration = $this->duration;

        return $duration ? FormatHelper::formatExecutionTime($duration) : null;
    }

    /**
     * 判断备份是否已过期
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * 获取过期状态文本
     */
    public function getExpiryStatusAttribute(): string
    {
        if (! $this->expires_at) {
            return '永不过期';
        }

        if ($this->is_expired) {
            return '已过期';
        }

        return '剩余 '.$this->expires_at->diffForHumans();
    }

    /**
     * 获取过期状态颜色
     */
    public function getExpiryColorAttribute(): string
    {
        if (! $this->expires_at) {
            return 'info';
        }

        if ($this->is_expired) {
            return 'danger';
        }

        $daysLeft = now()->diffInDays($this->expires_at);
        if ($daysLeft <= 3) {
            return 'warning';
        }

        return 'success';
    }

    /**
     * 获取文件扩展名
     */
    public function getFileExtensionAttribute(): string
    {
        $backupExt = $this->getBackupTypeEnumAttribute()->getFileExtension();
        $compressExt = $this->getCompressionTypeEnumAttribute()->getFileExtension();

        return $backupExt.$compressExt;
    }

    /**
     * 获取完整文件名
     */
    public function getFullFilenameAttribute(): string
    {
        return $this->backup_name.'.'.$this->file_extension;
    }

    /**
     * 判断文件是否存在
     */
    public function getFileExistsAttribute(): bool
    {
        return file_exists($this->backup_path);
    }

    /**
     * 关联清理计划
     *
     * 当 source_type = CLEANUP_PLAN 时有效
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(CleanupPlan::class, 'plan_id')
            ->where('source_type', BACKUP_SOURCE_TYPE::CLEANUP_PLAN->value);
    }

    /**
     * 关联独立备份计划
     *
     * 当 source_type = BACKUP_PLAN 时有效
     */
    public function backupPlan(): BelongsTo
    {
        return $this->belongsTo(CleanupBackupPlan::class, 'plan_id')
            ->where('source_type', BACKUP_SOURCE_TYPE::BACKUP_PLAN->value);
    }

    /**
     * 关联清理任务
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(CleanupTask::class, 'task_id');
    }

    /**
     * 关联备份文件
     */
    public function files(): HasMany
    {
        return $this->hasMany(CleanupBackupFile::class, 'backup_id');
    }

    /**
     * 关联SQL备份记录
     */
    public function sqlBackups(): HasMany
    {
        return $this->hasMany(CleanupSqlBackup::class, 'backup_id');
    }

    /**
     * 作用域：按计划筛选
     */
    public function scopeByPlan($query, int $planId)
    {
        return $query->where('plan_id', $planId);
    }

    /**
     * 作用域：按备份来源类型筛选
     */
    public function scopeBySourceType($query, int $sourceType)
    {
        return $query->where('source_type', $sourceType);
    }

    /**
     * 作用域：按任务筛选
     */
    public function scopeByTask($query, int $taskId)
    {
        return $query->where('task_id', $taskId);
    }

    /**
     * 作用域：按备份状态筛选
     */
    public function scopeByStatus($query, int $status)
    {
        return $query->where('backup_status', $status);
    }

    /**
     * 作用域：已完成的备份
     */
    public function scopeCompleted($query)
    {
        return $query->where('backup_status', BACKUP_STATUS::COMPLETED->value);
    }

    /**
     * 作用域：已过期的备份
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * 作用域：即将过期的备份
     */
    public function scopeExpiringSoon($query, int $days = 7)
    {
        return $query->whereBetween('expires_at', [now(), now()->addDays($days)]);
    }

    /**
     * 作用域：按备份名称搜索
     */
    public function scopeSearchName($query, string $search)
    {
        return $query->where('backup_name', 'like', "%{$search}%");
    }

    /**
     * 格式化字节大小
     */
    /**
     * 获取备份状态统计
     */
    public static function getStatusStats(): array
    {
        $stats = static::selectRaw('backup_status, COUNT(*) as count')
            ->groupBy('backup_status')
            ->get()
            ->keyBy('backup_status')
            ->toArray();

        $result = [];
        foreach (BACKUP_STATUS::cases() as $status) {
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
     * 获取存储统计
     */
    public static function getStorageStats(): array
    {
        $stats = static::selectRaw('
            COUNT(*) as total_backups,
            SUM(backup_size) as total_backup_size,
            SUM(original_size) as total_original_size,
            AVG(backup_size) as avg_backup_size
        ')->first();

        return [
            'total_backups' => $stats->total_backups ?? 0,
            'total_backup_size' => $stats->total_backup_size ?? 0,
            'total_original_size' => $stats->total_original_size ?? 0,
            'avg_backup_size' => $stats->avg_backup_size ?? 0,
            'total_compression_ratio' => $stats->total_original_size > 0
                ? (1 - $stats->total_backup_size / $stats->total_original_size) * 100
                : 0,
        ];
    }
}
