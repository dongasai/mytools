<?php

namespace Modules\FeatureAi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * AI服务提供商配置模型.
 *
 * @property int $id 主键ID
 * @property string $provider_type 提供商类型:openai/claude/gemini/custom
 * @property string $provider_name 提供商名称
 * @property string $api_key API密钥(加密存储)
 * @property string|null $api_endpoint API端点URL
 * @property int $is_active 是否启用:1启用,2禁用
 * @property int $priority 优先级:数字越大优先级越高
 * @property array|null $config_json 其他配置参数(JSON格式)
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property \Illuminate\Support\Carbon|null $deleted_at 软删除时间
 */
class AiProvider extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * 表名.
     */
    protected $table = 'ai_providers';

    /**
     * 可批量赋值的字段.
     */
    protected $fillable = [
        'provider_type',
        'provider_name',
        'api_key',
        'api_endpoint',
        'is_active',
        'priority',
        'config_json',
    ];

    /**
     * 字段类型转换.
     */
    protected $casts = [
        'config_json' => 'array',
        'is_active' => 'integer',
        'priority' => 'integer',
    ];

    /**
     * 关联：一个提供商有多个模型.
     */
    public function models(): HasMany
    {
        return $this->hasMany(AiProviderModel::class, 'provider_id', 'id');
    }
}
