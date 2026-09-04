<?php

namespace Modules\FeatureAi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 动态Agent执行记录模型.
 *
 * @property int $id 主键ID
 * @property int $agent_id Agent ID
 * @property string|null $workflow_id NeuronAI Workflow ID
 * @property int|null $user_id 执行用户ID
 * @property string|null $input_message 输入消息
 * @property string|null $output_message 输出消息
 * @property string $status 执行状态:success/failed/interrupted
 * @property array|null $tools_called 调用的工具列表
 * @property int|null $duration_ms 执行时长（毫秒）
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property DynamicAgent $agent 关联的Agent
 */
class DynamicAgentExecution extends Model
{
    use HasFactory;

    /**
     * 表名.
     */
    protected $table = 'featureai_dynamic_agent_executions';

    /**
     * 可批量赋值的字段.
     */
    protected $fillable = [
        'agent_id',
        'workflow_id',
        'user_id',
        'input_message',
        'output_message',
        'status',
        'tools_called',
        'duration_ms',
    ];

    /**
     * 字段类型转换.
     */
    protected $casts = [
        'agent_id' => 'integer',
        'user_id' => 'integer',
        'tools_called' => 'array',
        'duration_ms' => 'integer',
    ];

    /**
     * 指示模型是否自动维护时间戳.
     * 因为created_at手动维护，不设置updated_at.
     */
    public $timestamps = false;

    /**
     * 关联：执行记录属于一个Agent.
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(DynamicAgent::class, 'agent_id', 'id');
    }

    /**
     * 是否执行成功.
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    /**
     * 是否执行失败.
     *
     * @return bool
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * 是否被中断.
     *
     * @return bool
     */
    public function isInterrupted(): bool
    {
        return $this->status === 'interrupted';
    }
}
