<?php

namespace Modules\FeatureAi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * LLM API调用日志模型.
 *
 * @property int $id 主键ID
 * @property int $provider_id 提供商ID
 * @property int $model_id 模型ID
 * @property string $model_name 模型名称(冗余,便于查询)
 * @property string $request_id 请求ID(唯一,对应日志文件)
 * @property string $service_type 服务类型(如chat,image等)
 * @property string $service_name 服务名称(具体服务标识)
 * @property int $input_tokens 输入tokens数量
 * @property int $output_tokens 输出tokens数量
 * @property int $total_tokens 总tokens数量
 * @property string $input_cost 输入成本(美元,高精度)
 * @property string $output_cost 输出成本(美元,高精度)
 * @property string $total_cost 总成本(美元,高精度)
 * @property int $response_time_ms 响应时间(毫秒)
 * @property bool $success 是否成功:1成功,0失败
 * @property string|null $error_type 错误类型(失败时记录)
 * @property string|null $error_message 错误详细信息(失败时记录)
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 *
 * @property-read \Modules\FeatureAi\Models\AiProvider $provider 关联的提供商
 * @property-read \Modules\FeatureAi\Models\AiProviderModel $model 关联的模型
 */
class LlmApiLog extends Model
{
    use HasFactory;

    /**
     * 表名.
     */
    protected $table = 'ai_llm_api_logs';

    /**
     * 可批量赋值的字段.
     */
    protected $fillable = [
        'provider_id',
        'model_id',
        'model_name',
        'request_id',
        'service_type',
        'service_name',
        'input_tokens',
        'output_tokens',
        'total_tokens',
        'input_cost',
        'output_cost',
        'total_cost',
        'response_time_ms',
        'success',
        'error_type',
        'error_message',
    ];

    /**
     * 字段类型转换.
     */
    protected $casts = [
        'provider_id' => 'integer',
        'model_id' => 'integer',
        'input_tokens' => 'integer',
        'output_tokens' => 'integer',
        'total_tokens' => 'integer',
        'response_time_ms' => 'integer',
        'success' => 'boolean',
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
