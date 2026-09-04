<?php

namespace Modules\FeatureAi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Collection;

/**
 * 动态Agent定义模型.
 *
 * @property int $id 主键ID
 * @property string $name Agent名称
 * @property string $slug URL友好标识
 * @property string|null $description 描述
 * @property bool $is_active 是否启用
 * @property string $provider_class Provider类名
 * @property array|null $provider_config Provider配置（api_key, model等）
 * @property string $instructions 系统提示词
 * @property int $tool_max_runs 工具最大调用次数
 * @property bool $parallel_tool_calls 是否并行执行工具
 * @property string $persistence_driver 持久化驱动:database/file/memory
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property Collection|DynamicAgentTool[] $tools 关联的工具列表
 * @property Collection|DynamicAgentExecution[] $executions 关联的执行记录
 */
class DynamicAgent extends Model
{
    use HasFactory;

    /**
     * 表名.
     */
    protected $table = 'featureai_dynamic_agents';

    /**
     * 可批量赋值的字段.
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'provider_class',
        'provider_config',
        'instructions',
        'tool_max_runs',
        'parallel_tool_calls',
        'persistence_driver',
    ];

    /**
     * 字段类型转换.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'provider_config' => 'array',
        'tool_max_runs' => 'integer',
        'parallel_tool_calls' => 'boolean',
    ];

    /**
     * 关联：一个Agent有多个工具.
     */
    public function tools(): HasMany
    {
        return $this->hasMany(DynamicAgentTool::class, 'agent_id', 'id')
            ->orderBy('order_index');
    }

    /**
     * 关联：一个Agent有多个执行记录.
     */
    public function executions(): HasMany
    {
        return $this->hasMany(DynamicAgentExecution::class, 'agent_id', 'id')
            ->orderByDesc('created_at');
    }

    /**
     * 获取启用的工具列表.
     *
     * @return Collection|DynamicAgentTool[]
     */
    public function enabledTools(): Collection
    {
        return $this->tools()->where('is_enabled', true)->get();
    }
}
