<?php

namespace Modules\FeatureAi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * AI模型配置模型.
 *
 * @property int $id 主键ID
 * @property int $provider_id 提供商ID(关联ai_providers.id)
 * @property string $model_name 模型名称:gpt-4/claude-3-opus等
 * @property string $model_type 模型类型:chat/image/embedding
 * @property int $max_tokens 最大tokens限制
 * @property string $cost_per_input_token 输入tokens单价(美元)
 * @property string $cost_per_output_token 输出tokens单价(美元)
 * @property int $is_active 是否启用:1启用,2禁用
 * @property array|null $config_json 模型特定配置(JSON格式)
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property \Illuminate\Support\Carbon|null $deleted_at 软删除时间
 */
class AiProviderModel extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * 表名.
     */
    protected $table = 'ai_provider_models';

    /**
     * 可批量赋值的字段.
     */
    protected $fillable = [
        'provider_id',
        'model_name',
        'model_type',
        'max_tokens',
        'cost_per_input_token',
        'cost_per_output_token',
        'is_active',
        'config_json',
    ];

    /**
     * 字段类型转换.
     */
    protected $casts = [
        'config_json' => 'array',
        'provider_id' => 'integer',
        'max_tokens' => 'integer',
        'is_active' => 'integer',
    ];

    /**
     * 关联：属于某个提供商.
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(AiProvider::class, 'provider_id', 'id');
    }

    /**
     * 关联：一个模型有多个对话记录.
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(AiConversation::class, 'model_id', 'id');
    }
}
