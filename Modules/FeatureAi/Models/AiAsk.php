<?php

namespace Modules\FeatureAi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * AI Ask 问题模型.
 *
 * @property int $id
 * @property string $ask_id 问题唯一ID
 * @property string $workflow_id Workflow ID
 * @property string|null $job_id 队列 Job ID
 * @property string $question 问题内容
 * @property string|null $context 上下文
 * @property string|null $default 默认值
 * @property string $status 状态: pending/answered/timeout/cancelled
 * @property string|null $answer 答案
 * @property \Carbon\Carbon|null $answered_at 回答时间
 * @property int|null $answered_by 回答用户ID
 * @property string $agent_class Agent 类名
 * @property array|null $agent_state Agent 状态快照
 * @property array|null $metadata 元数据
 * @property \Carbon\Carbon|null $expires_at 过期时间
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class AiAsk extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ask_id',
        'workflow_id',
        'job_id',
        'question',
        'context',
        'default',
        'status',
        'answer',
        'answered_at',
        'answered_by',
        'agent_class',
        'agent_state',
        'metadata',
        'expires_at',
    ];

    protected $casts = [
        'agent_state' => 'array',
        'metadata' => 'array',
        'answered_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * 状态常量.
     */
    const STATUS_PENDING = 'pending';
    const STATUS_ANSWERED = 'answered';
    const STATUS_TIMEOUT = 'timeout';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * 是否待回答.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * 是否已回答.
     *
     * @return bool
     */
    public function isAnswered(): bool
    {
        return $this->status === self::STATUS_ANSWERED;
    }

    /**
     * 是否已过期.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * 标记为已回答.
     *
     * @param string $answer 答案
     * @param int|null $userId 用户ID
     *
     * @return void
     */
    public function markAsAnswered(string $answer, ?int $userId = null): void
    {
        $this->update([
            'status' => self::STATUS_ANSWERED,
            'answer' => $answer,
            'answered_at' => now(),
            'answered_by' => $userId,
        ]);
    }

    /**
     * 标记为超时.
     *
     * @return void
     */
    public function markAsTimeout(): void
    {
        $this->update([
            'status' => self::STATUS_TIMEOUT,
        ]);
    }

    /**
     * 标记为取消.
     *
     * @return void
     */
    public function markAsCancelled(): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
        ]);
    }

    /**
     * 获取待回答的问题.
     *
     * @param string $askId
     *
     * @return static|null
     */
    public static function getPendingAsk(string $askId): ?self
    {
        return static::where('ask_id', $askId)
            ->where('status', self::STATUS_PENDING)
            ->first();
    }

    /**
     * 获取工作流的所有待回答问题.
     *
     * @param string $workflowId
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getPendingAsksByWorkflow(string $workflowId)
    {
        return static::where('workflow_id', $workflowId)
            ->where('status', self::STATUS_PENDING)
            ->orderBy('created_at')
            ->get();
    }

    /**
     * 清理过期问题.
     *
     * @return int 清理数量
     */
    public static function cleanupExpired(): int
    {
        return static::where('status', self::STATUS_PENDING)
            ->where('expires_at', '<', now())
            ->update(['status' => self::STATUS_TIMEOUT]);
    }
}