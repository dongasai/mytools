<?php

namespace Modules\FeatureAi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * AI集成测试记录模型.
 *
 * @property int $id 主键ID
 * @property int $provider_id 提供商ID
 * @property int $model_id 模型ID
 * @property string $test_type 测试类型:connect/response/cost/image
 * @property string $test_name 测试名称
 * @property array|null $test_config_json 测试配置(JSON格式)
 * @property int $status 状态:1待执行,2执行中,3成功,4失败
 * @property int $total_tests 总测试次数
 * @property int $success_count 成功次数
 * @property int $fail_count 失败次数
 * @property int $avg_response_time_ms 平均响应时间(毫秒)
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 */
class AiTest extends Model
{
    use HasFactory;

    /**
     * 表名.
     */
    protected $table = 'ai_tests';

    /**
     * 可批量赋值的字段.
     */
    protected $fillable = [
        'provider_id',
        'model_id',
        'test_type',
        'test_name',
        'test_config_json',
        'status',
        'total_tests',
        'success_count',
        'fail_count',
        'avg_response_time_ms',
    ];

    /**
     * 字段类型转换.
     */
    protected $casts = [
        'test_config_json' => 'array',
        'provider_id' => 'integer',
        'model_id' => 'integer',
        'status' => 'integer',
        'total_tests' => 'integer',
        'success_count' => 'integer',
        'fail_count' => 'integer',
        'avg_response_time_ms' => 'integer',
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

    /**
     * 关联：一个测试有多个测试结果.
     */
    public function results(): HasMany
    {
        return $this->hasMany(AiTestResult::class, 'test_id', 'id');
    }
}
