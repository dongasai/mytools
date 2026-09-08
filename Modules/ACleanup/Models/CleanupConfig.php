<?php

namespace Modules\AClean\Models;

use Modules\AClean\Enums\CLEANUP_TYPE;
use Modules\AClean\Enums\DATA_CATEGORY;
use Illuminate\Database\Eloquent\Model;

/**
 * 清理配置模型
 *
 * 存储每个数据表的基础清理配置信息
 * field start
 *
 * @property int $id 主键ID
 * @property string $table_name 表名
 * @property string $model_class Model类名
 * @property array $model_info Model类信息
 * @property string $module_name 模块名称
 * @property int $data_category 数据分类:1用户数据,2日志数据,3交易数据,4缓存数据,5配置数据
 * @property int $default_cleanup_type 默认清理类型:1清空表,2删除所有,3按时间删除,4按用户删除,5按条件删除
 * @property array $default_conditions 默认清理条件JSON配置
 * @property bool $is_enabled 是否启用清理
 * @property int $priority 清理优先级(数字越小优先级越高)
 * @property int $batch_size 批处理大小
 * @property string $description 配置描述
 * @property \Carbon\Carbon $last_cleanup_at 最后清理时间
 * @property \Carbon\Carbon $created_at 创建时间
 * @property \Carbon\Carbon $updated_at 更新时间
 *                                      field end
 */
class CleanupConfig extends Model
{
    /**
     * 数据表名
     */
    protected $table = 'cleanup_configs';

    // attrlist start
    protected $fillable = [
        'id',
        'table_name',
        'model_class',
        'model_info',
        'module_name',
        'data_category',
        'default_cleanup_type',
        'default_conditions',
        'is_enabled',
        'priority',
        'batch_size',
        'description',
        'last_cleanup_at',
    ];
    // attrlist end

    /**
     * 字段类型转换
     */
    protected $casts = [
        'model_info' => 'array',
        'data_category' => 'integer',
        'default_cleanup_type' => 'integer',
        'default_conditions' => 'array',
        'is_enabled' => 'boolean',
        'priority' => 'integer',
        'batch_size' => 'integer',
        'last_cleanup_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 获取数据分类枚举
     */
    public function getDataCategoryEnumAttribute(): DATA_CATEGORY
    {
        return DATA_CATEGORY::from($this->data_category);
    }

    /**
     * 获取默认清理类型枚举
     */
    public function getDefaultCleanupTypeEnumAttribute(): CLEANUP_TYPE
    {
        return CLEANUP_TYPE::from($this->default_cleanup_type);
    }

    /**
     * 获取数据分类描述
     */
    public function getDataCategoryNameAttribute(): string
    {
        return $this->getDataCategoryEnumAttribute()->getDescription();
    }

    /**
     * 获取默认清理类型描述
     */
    public function getDefaultCleanupTypeNameAttribute(): string
    {
        return $this->getDefaultCleanupTypeEnumAttribute()->getDescription();
    }

    /**
     * 获取数据分类颜色
     */
    public function getDataCategoryColorAttribute(): string
    {
        return $this->getDataCategoryEnumAttribute()->getColor();
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
        return $this->getDefaultCleanupTypeEnumAttribute()->needsConditions();
    }

    /**
     * 判断是否支持回滚
     */
    public function getIsRollbackableAttribute(): bool
    {
        return $this->getDefaultCleanupTypeEnumAttribute()->isRollbackable();
    }

    /**
     * 获取格式化的最后清理时间
     */
    public function getLastCleanupAtFormattedAttribute(): ?string
    {
        return $this->last_cleanup_at ? $this->last_cleanup_at->format('Y-m-d H:i:s') : null;
    }

    /**
     * 获取最后清理时间的相对时间
     */
    public function getLastCleanupAtHumanAttribute(): ?string
    {
        return $this->last_cleanup_at ? $this->last_cleanup_at->diffForHumans() : '从未清理';
    }

    /**
     * 作用域：按模块筛选
     */
    public function scopeByModule($query, string $moduleName)
    {
        return $query->where('module_name', $moduleName);
    }

    /**
     * 作用域：按数据分类筛选
     */
    public function scopeByCategory($query, int $category)
    {
        return $query->where('data_category', $category);
    }

    /**
     * 作用域：只查询启用的配置
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
     * 作用域：按表名搜索
     */
    public function scopeSearchTable($query, string $search)
    {
        return $query->where('table_name', 'like', "%{$search}%");
    }

    /**
     * 作用域：排除配置数据
     */
    public function scopeExcludeConfig($query)
    {
        return $query->where('data_category', '!=', DATA_CATEGORY::CONFIG_DATA->value);
    }

    /**
     * 作用域：只查询有Model类的配置
     */
    public function scopeWithModel($query)
    {
        return $query->whereNotNull('model_class')->where('model_class', '!=', '');
    }

    /**
     * 作用域：只查询没有Model类的配置（旧数据）
     */
    public function scopeWithoutModel($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('model_class')->orWhere('model_class', '');
        });
    }
}
