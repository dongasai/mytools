<?php

namespace Modules\AClean\Models;

use Modules\AClean\Enums\CLEANUP_TYPE;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 清理日志模型
 *
 * 记录每个表的清理操作详情
 * field start
 *
 * @property int $id 主键ID
 * @property int $task_id 任务ID
 * @property string $table_name 表名
 * @property string $model_class Model类名
 * @property int $cleanup_type 清理类型:1清空表,2删除所有,3按时间删除,4按用户删除,5按条件删除
 * @property int $before_count 清理前记录数
 * @property int $after_count 清理后记录数
 * @property int $deleted_records 删除记录数
 * @property float $execution_time 执行时间(秒)
 * @property array $conditions 使用的清理条件
 * @property string $error_message 错误信息
 * @property \Carbon\Carbon $created_at 创建时间
 *                                      field end
 */
class CleanupLog extends Model
{
    /**
     * 数据表名
     */
    protected $table = 'cleanup_logs';

    // attrlist start
    protected $fillable = [
        'id',
        'task_id',
        'table_name',
        'model_class',
        'cleanup_type',
        'before_count',
        'after_count',
        'deleted_records',
        'execution_time',
        'conditions',
        'error_message',
    ];
    // attrlist end

    /**
     * 字段类型转换
     */
    protected $casts = [
        'task_id' => 'integer',
        'cleanup_type' => 'integer',
        'before_count' => 'integer',
        'after_count' => 'integer',
        'deleted_records' => 'integer',
        'execution_time' => 'float',
        'conditions' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 获取清理类型枚举
     */
    public function getCleanupTypeEnumAttribute(): CLEANUP_TYPE
    {
        return CLEANUP_TYPE::from($this->cleanup_type);
    }

    /**
     * 关联任务
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(CleanupTask::class, 'task_id');
    }

    /**
     * 获取成功的日志
     */
    public function scopeSuccessful($query)
    {
        return $query->whereNull('error_message');
    }

    /**
     * 获取失败的日志
     */
    public function scopeFailed($query)
    {
        return $query->whereNotNull('error_message');
    }

    /**
     * 按表名筛选
     */
    public function scopeByTable($query, string $tableName)
    {
        return $query->where('table_name', $tableName);
    }

    /**
     * 按清理类型筛选
     */
    public function scopeByCleanupType($query, CLEANUP_TYPE $cleanupType)
    {
        return $query->where('cleanup_type', $cleanupType->value);
    }
}
