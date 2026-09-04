<?php

namespace Modules\FeatureAi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AI图片生成记录模型.
 *
 * @property int $id 主键ID
 * @property int $provider_id 提供商ID
 * @property int $model_id 模型ID
 * @property int $user_id 用户ID
 * @property string $prompt_text 图片生成提示文本
 * @property string|null $image_url 生成的图片URL
 * @property string|null $image_path 图片存储路径(本地或云端)
 * @property string|null $image_size 图片尺寸(如1024x1024)
 * @property string $cost 生成成本(美元)
 * @property int $status 状态:1待处理,2生成中,3成功,4失败
 * @property string|null $error_message 错误信息
 * @property int $retry_count 重试次数
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property \Illuminate\Support\Carbon|null $deleted_at 软删除时间
 */
class AiImage extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * 表名.
     */
    protected $table = 'ai_images';

    /**
     * 可批量赋值的字段.
     */
    protected $fillable = [
        'provider_id',
        'model_id',
        'user_id',
        'prompt_text',
        'image_url',
        'image_path',
        'image_size',
        'cost',
        'status',
        'error_message',
        'retry_count',
    ];

    /**
     * 字段类型转换.
     */
    protected $casts = [
        'provider_id' => 'integer',
        'model_id' => 'integer',
        'user_id' => 'integer',
        'status' => 'integer',
        'retry_count' => 'integer',
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
