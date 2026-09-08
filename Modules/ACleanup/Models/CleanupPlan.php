<?php

namespace Modules\AClean\Models;

use Modules\AClean\Enums\PLAN_TYPE;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 清理计划模型
 *
 * 存储清理计划信息，如"农场模块清理"
 * field start
 *
 * @property int $id 主键ID
 * @property string $plan_name 计划名称
 * @property int $plan_type 计划类型:1全量清理,2模块清理,3分类清理,4自定义清理,5混合清理
 * @property array $selected_tables 选择的Model类列表，格式：["Modules\System\Models\AdminActionlog"]
 * @property array $global_conditions 全局清理条件
 * @property array $backup_config 备份配置
 * @property bool $is_template 是否为模板
 * @property bool $is_enabled 是否启用
 * @property string $description 计划描述
 * @property int $created_by 创建者用户ID
 * @property \Carbon\Carbon $created_at 创建时间
 * @property \Carbon\Carbon $updated_at 更新时间
 *                                      field end
 */
class CleanupPlan extends Model
{
    /**
     * 数据表名
     */
    protected $table = 'cleanup_plans';

    /**
     * 字段类型转换
     */
    protected $casts = [
        'plan_type' => 'integer',
        'selected_tables' => 'array',
        'global_conditions' => 'array',
        'backup_config' => 'array',
        'is_template' => 'boolean',
        'is_enabled' => 'boolean',
        'created_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 获取计划类型枚举
     *
     * @return PLAN_TYPE
     */
    public function getPlanTypeEnumAttribute(): PLAN_TYPE
    {
        return PLAN_TYPE::from($this->plan_type);
    }

    /**
     * 获取计划类型颜色
     */
    public function getPlanTypeColorAttribute(): string
    {
        return $this->getPlanTypeEnumAttribute()->getColor();
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
     * 获取模板状态文本
     */
    public function getTemplateTextAttribute(): string
    {
        return $this->is_template ? '模板' : '计划';
    }

    /**
     * 获取模板状态颜色
     */
    public function getTemplateColorAttribute(): string
    {
        return $this->is_template ? 'info' : 'primary';
    }

    /**
     * 判断是否需要目标选择配置
     */
    public function getNeedsTargetSelectionAttribute(): bool
    {
        return $this->getPlanTypeEnumAttribute()->needsTargetSelection();
    }

    /**
     * 获取目标选择类型
     */
    public function getSelectionTypeAttribute(): ?string
    {
        return $this->target_selection['selection_type'] ?? null;
    }

    /**
     * 获取目标模块列表
     */
    public function getTargetModulesttribute(): array
    {
        return $this->target_selection['modules'] ?? [];
    }

    /**
     * 获取目标分类列表
     */
    public function getTargetCategoriesAttribute(): array
    {
        return $this->target_selection['categories'] ?? [];
    }

    /**
     * 获取目标表列表
     */
    public function getTargetTablesAttribute(): array
    {
        return $this->target_selection['tables'] ?? [];
    }

    /**
     * 获取排除表列表
     */
    public function getExcludeTablesAttribute(): array
    {
        return $this->target_selection['exclude_tables'] ?? [];
    }

    /**
     * 获取排除模块列表
     */
    public function getExcludeModulesttribute(): array
    {
        return $this->target_selection['exclude_modules'] ?? [];
    }

    /**
     * 获取排除分类列表
     */
    public function getExcludeCategoriesAttribute(): array
    {
        return $this->target_selection['exclude_categories'] ?? [];
    }

    /**
     * 获取备份类型
     */
    public function getBackupTypeAttribute(): ?int
    {
        return $this->backup_config['backup_type'] ?? null;
    }

    /**
     * 获取压缩类型
     */
    public function getCompressionTypeAttribute(): ?int
    {
        return $this->backup_config['compression_type'] ?? null;
    }

    /**
     * 判断是否包含表结构
     */
    public function getIncludeStructureAttribute(): bool
    {
        return $this->backup_config['include_structure'] ?? true;
    }

    /**
     * 关联计划内容
     */
    public function contents(): HasMany
    {
        return $this->hasMany(CleanupPlanContent::class, 'plan_id');
    }

    /**
     * 关联启用的计划内容
     */
    public function enabledContents(): HasMany
    {
        return $this->contents()->where('is_enabled', true);
    }

    /**
     * 关联清理任务
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(CleanupTask::class, 'plan_id');
    }

    /**
     * 关联备份记录
     */
    public function backups(): HasMany
    {
        return $this->hasMany(CleanupBackup::class, 'plan_id');
    }

    /**
     * 作用域：按计划类型筛选
     */
    public function scopeByType($query, int $type)
    {
        return $query->where('plan_type', $type);
    }

    /**
     * 作用域：只查询启用的计划
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * 作用域：只查询模板
     */
    public function scopeTemplates($query)
    {
        return $query->where('is_template', true);
    }

    /**
     * 作用域：只查询非模板
     */
    public function scopeNonTemplates($query)
    {
        return $query->where('is_template', false);
    }

    /**
     * 作用域：按计划名称搜索
     */
    public function scopeSearchName($query, string $search)
    {
        return $query->where('plan_name', 'like', "%{$search}%");
    }

    /**
     * 作用域：按创建者筛选
     */
    public function scopeByCreator($query, int $createdBy)
    {
        return $query->where('created_by', $createdBy);
    }

    /**
     * 获取计划内容数量
     */
    public function getContentsCountAttribute(): int
    {
        return $this->contents()->count();
    }

    /**
     * 获取启用的内容数量
     */
    public function getEnabledContentsCountAttribute(): int
    {
        return $this->enabledContents()->count();
    }

    /**
     * 获取任务数量
     */
    public function getTasksCountAttribute(): int
    {
        return $this->tasks()->count();
    }

    /**
     * 获取备份数量
     */
    public function getBackupsCountAttribute(): int
    {
        return $this->backups()->count();
    }

    /**
     * 获取最后执行时间
     */
    public function getLastExecutedAtAttribute(): ?string
    {
        $lastTask = $this->tasks()
            ->whereNotNull('completed_at')
            ->orderBy('completed_at', 'desc')
            ->first();

        return $lastTask ? $lastTask->completed_at->format('Y-m-d H:i:s') : null;
    }

}
