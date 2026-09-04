<?php

namespace Modules\FeatureAi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AI对话记录模型.
 *
 * @property int $id 主键ID
 * @property int $provider_id 提供商ID
 * @property int $model_id 模型ID
 * @property int|null $user_id 用户ID(后台测试时可为NULL)
 * @property string $conversation_id 对话会话ID(多轮对话标识)
 * @property string $prompt_text 用户输入的提示文本
 * @property string|null $response_text AI返回的响应文本
 * @property int $input_tokens 输入tokens数量
 * @property int $output_tokens 输出tokens数量
 * @property string $total_cost 总成本(美元)
 * @property int $status 状态:1进行中,2已完成,3失败
 * @property string|null $error_message 错误信息(失败时记录)
 * @property int $response_time_ms 响应时间(毫秒)
 * @property array|null $context_json 对话上下文(JSON格式)
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 */
class AiConversation extends Model
{
    use HasFactory;

    /**
     * 表名.
     */
    protected $table = 'ai_conversations';

    /**
     * 可批量赋值的字段.
     */
    protected $fillable = [
        'provider_id',
        'model_id',
        'user_id',
        'conversation_id',
        'prompt_text',
        'response_text',
        'input_tokens',
        'output_tokens',
        'total_cost',
        'status',
        'error_message',
        'response_time_ms',
        'context_json',
    ];

    /**
     * 字段类型转换.
     */
    protected $casts = [
        'context_json' => 'array',
        'provider_id' => 'integer',
        'model_id' => 'integer',
        'user_id' => 'integer',
        'input_tokens' => 'integer',
        'output_tokens' => 'integer',
        'status' => 'integer',
        'response_time_ms' => 'integer',
    ];

    /**
     * 关联：属于某个提供商.
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(AiProvider::class, 'provider_id', 'id');
    }

    /**
     * 关联：属于某个模型.
     */
    public function model(): BelongsTo
    {
        return $this->belongsTo(AiProviderModel::class, 'model_id', 'id');
    }
}
