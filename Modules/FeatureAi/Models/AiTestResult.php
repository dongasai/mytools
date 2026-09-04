<?php

namespace Modules\FeatureAi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AI测试结果详情模型.
 *
 * @property int $id 主键ID
 * @property int $test_id 测试记录ID
 * @property int $test_sequence 测试序号
 * @property int $is_success 是否成功:1成功,2失败
 * @property int $response_time_ms 响应时间(毫秒)
 * @property int $input_tokens 输入tokens
 * @property int $output_tokens 输出tokens
 * @property string $cost 成本(美元)
 * @property string|null $response_text 响应文本
 * @property string|null $error_message 错误信息
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 */
class AiTestResult extends Model
{
    use HasFactory;

    /**
     * 表名.
     */
    protected $table = 'ai_test_results';

    /**
     * 只使用 created_at，不使用 updated_at.
     */
    const UPDATED_AT = null;

    /**
     * 可批量赋值的字段.
     */
    protected $fillable = [
        'test_id',
        'test_sequence',
        'is_success',
        'response_time_ms',
        'input_tokens',
        'output_tokens',
        'cost',
        'response_text',
        'error_message',
    ];

    /**
     * 字段类型转换.
     */
    protected $casts = [
        'test_id' => 'integer',
        'test_sequence' => 'integer',
        'is_success' => 'integer',
        'response_time_ms' => 'integer',
        'input_tokens' => 'integer',
        'output_tokens' => 'integer',
    ];

    /**
     * 关联：属于某个测试记录.
     */
    public function test(): BelongsTo
    {
        return $this->belongsTo(AiTest::class, 'test_id', 'id');
    }
}
