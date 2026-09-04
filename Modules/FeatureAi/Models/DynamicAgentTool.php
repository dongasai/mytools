<?php

namespace Modules\FeatureAi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 动态Agent工具关联模型.
 *
 * @property int $id 主键ID
 * @property int $agent_id Agent ID
 * @property string $tool_class 工具类名
 * @property string|null $tool_name 工具名称
 * @property array|null $tool_config 工具配置参数
 * @property int $order_index 排序
 * @property bool $is_enabled 是否启用
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property DynamicAgent $agent 关联的Agent
 */
class DynamicAgentTool extends Model
{
    use HasFactory;

    /**
     * 表名.
     */
    protected $table = 'featureai_dynamic_agent_tools';

    /**
     * 可批量赋值的字段.
     */
    protected $fillable = [
        'agent_id',
        'tool_class',
        'tool_name',
        'tool_config',
        'order_index',
        'is_enabled',
    ];

    /**
     * 字段类型转换.
     */
    protected $casts = [
        'agent_id' => 'integer',
        'tool_config' => 'array',
        'order_index' => 'integer',
        'is_enabled' => 'boolean',
    ];

    /**
     * 关联：工具属于一个Agent.
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(DynamicAgent::class, 'agent_id', 'id');
    }
}
