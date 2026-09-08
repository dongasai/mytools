<?php

namespace Modules\AClean\Models;

use Modules\AClean\Enums\CLEANUP_TYPE;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 计划内容模型
 *
 * 存储计划的具体内容，即计划具体处理哪些表，怎么清理
 * field start
 *
 * @property int $id 主键ID
 * @property int $plan_id 计划ID
 * @property string $table_name 表名
 * @property string $model_class Model类名
 * @property int $cleanup_type 清理类型:1清空表,2删除所有,3按时间删除,4按用户删除,5按条件删除
 * @property array $conditions 清理条件JSON配置
 * @property int $priority 清理优先级
 * @property int $batch_size 批处理大小
 * @property bool $is_enabled 是否启用
 * @property bool $backup_enabled 是否启用备份
 * @property string $notes 备注说明
 * @property \Carbon\Carbon $created_at 创建时间
 * @property \Carbon\Carbon $updated_at 更新时间
 *                                      field end *
 */
class CleanupPlanContent extends Model
{
    /**
     * 数据表名
     */
    protected $table = 'cleanup_plan_contents';

    // attrlist start
    protected $fillable = [
        'id',
        'plan_id',
        'table_name',
        'model_class',
        'cleanup_type',
        'conditions',
        'priority',
        'batch_size',
        'is_enabled',
        'backup_enabled',
        'notes',
    ];
    // attrlist end

    /**
     * 字段类型转换
     */
    protected $casts = [
        'plan_id' => 'integer',
        'cleanup_type' => 'integer',
        'conditions' => 'array',
        'priority' => 'integer',
        'batch_size' => 'integer',
        'is_enabled' => 'boolean',
        'backup_enabled' => 'boolean',
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
     * 获取清理类型描述
     */
    public function getCleanupTypeNameAttribute(): string
    {
        return $this->getCleanupTypeEnumAttribute()->getDescription();
    }

    /**
     * 获取启用状态文本
     */
    public function getEnabledTextAttribute(): string
    {
        return $this->is_enabled ? '启用' : '禁用';
    }

    /**
     * 获取启用状态颜色
     */
    public function getEnabledColorAttribute(): string
    {
        return $this->is_enabled ? 'success' : 'secondary';
    }

    /**
     * 获取备份状态文本
     */
    public function getBackupTextAttribute(): string
    {
        return $this->backup_enabled ? '启用' : '禁用';
    }

    /**
     * 获取备份状态颜色
     */
    public function getBackupColorAttribute(): string
    {
        return $this->backup_enabled ? 'success' : 'warning';
    }

    /**
     * 获取优先级文本
     */
    public function getPriorityTextAttribute(): string
    {
        if ($this->priority <= 50) {
            return '高';
        } elseif ($this->priority <= 200) {
            return '中';
        } else {
            return '低';
        }
    }

    /**
     * 获取优先级颜色
     */
    public function getPriorityColorAttribute(): string
    {
        if ($this->priority <= 50) {
            return 'danger';
        } elseif ($this->priority <= 200) {
            return 'warning';
        } else {
            return 'info';
        }
    }

    /**
     * 判断是否需要条件配置
     */
    public function getNeedsConditionsAttribute(): bool
    {
        return $this->getCleanupTypeEnumAttribute()->needsConditions();
    }

    /**
     * 判断是否支持回滚
     */
    public function getIsRollbackableAttribute(): bool
    {
        return $this->getCleanupTypeEnumAttribute()->isRollbackable();
    }

    /**
     * 获取时间字段
     */
    public function getTimeFieldAttribute(): ?string
    {
        return $this->conditions['time_field'] ?? null;
    }

    /**
     * 获取时间条件
     */
    public function getTimeConditionAttribute(): ?string
    {
        return $this->conditions['time_condition'] ?? null;
    }

    /**
     * 获取时间值
     */
    public function getTimeValueAttribute(): ?int
    {
        return $this->conditions['time_value'] ?? null;
    }

    /**
     * 获取时间单位
     */
    public function getTimeUnitAttribute(): ?string
    {
        return $this->conditions['time_unit'] ?? null;
    }

    /**
     * 获取用户字段
     */
    public function getUserFieldAttribute(): ?string
    {
        return $this->conditions['user_field'] ?? null;
    }

    /**
     * 获取用户条件
     */
    public function getUserConditionAttribute(): ?string
    {
        return $this->conditions['user_condition'] ?? null;
    }

    /**
     * 获取用户值列表
     */
    public function getUserValuesAttribute(): array
    {
        return $this->conditions['user_values'] ?? [];
    }

    /**
     * 获取自定义条件
     */
    public function getCustomConditionsAttribute(): array
    {
        return $this->conditions['conditions'] ?? [];
    }

    /**
     * 获取条件逻辑
     */
    public function getConditionLogicAttribute(): string
    {
        return $this->conditions['logic'] ?? 'AND';
    }

    /**
     * 获取条件描述
     */
    public function getConditionsDescriptionAttribute(): string
    {
        if (! $this->needs_conditions || empty($this->conditions)) {
            return '无条件';
        }

        $descriptions = [];

        switch ($this->cleanup_type) {
            case CLEANUP_TYPE::DELETE_BY_TIME->value:
                if ($this->time_field && $this->time_value && $this->time_unit) {
                    $descriptions[] = "删除 {$this->time_field} 字段 {$this->time_value} {$this->time_unit} 前的记录";
                }
                break;

            case CLEANUP_TYPE::DELETE_BY_USER->value:
                if ($this->user_field && ! empty($this->user_values)) {
                    $userList = implode(', ', array_slice($this->user_values, 0, 3));
                    if (count($this->user_values) > 3) {
                        $userList .= ' 等'.count($this->user_values).'个用户';
                    }
                    $descriptions[] = "删除 {$this->user_field} 为 {$userList} 的记录";
                }
                break;

            case CLEANUP_TYPE::DELETE_BY_CONDITION->value:
                if (! empty($this->custom_conditions)) {
                    $descriptions[] = '自定义条件：'.count($this->custom_conditions).' 个条件';
                }
                break;
        }

        return empty($descriptions) ? '条件配置不完整' : implode('; ', $descriptions);
    }

    /**
     * 关联清理计划
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(CleanupPlan::class, 'plan_id');
    }

    /**
     * 关联清理配置
     */
    public function config(): BelongsTo
    {
        return $this->belongsTo(CleanupConfig::class, 'table_name', 'table_name');
    }

    /**
     * 作用域：按计划筛选
     */
    public function scopeByPlan($query, int $planId)
    {
        return $query->where('plan_id', $planId);
    }

    /**
     * 作用域：按表名筛选
     */
    public function scopeByTable($query, string $tableName)
    {
        return $query->where('table_name', $tableName);
    }

    /**
     * 作用域：只查询启用的内容
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * 作用域：按优先级排序
     */
    public function scopeOrderByPriority($query)
    {
        return $query->orderBy('priority')->orderBy('table_name');
    }

    /**
     * 作用域：按清理类型筛选
     */
    public function scopeByCleanupType($query, int $type)
    {
        return $query->where('cleanup_type', $type);
    }

    /**
     * 作用域：启用备份的内容
     */
    public function scopeBackupEnabled($query)
    {
        return $query->where('backup_enabled', true);
    }

    /**
     * 作用域：按表名搜索
     */
    public function scopeSearchTable($query, string $search)
    {
        return $query->where('table_name', 'like', "%{$search}%");
    }

    /**
     * 作用域：只查询有Model类的内容
     */
    public function scopeWithModel($query)
    {
        return $query->whereNotNull('model_class')->where('model_class', '!=', '');
    }

    /**
     * 作用域：只查询没有Model类的内容（旧数据）
     */
    public function scopeWithoutModel($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('model_class')->orWhere('model_class', '');
        });
    }

    /**
     * 作用域：按Model类筛选
     */
    public function scopeByModel($query, string $modelClass)
    {
        return $query->where('model_class', $modelClass);
    }
}
