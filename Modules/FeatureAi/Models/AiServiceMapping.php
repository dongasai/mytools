<?php

namespace Modules\FeatureAi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AI服务映射模型.
 *
 * 服务类型+服务名字到供应商的映射关系管理
 *
 * @property int $id 主键ID
 * @property string $service_type 服务类型，如a/b等
 * @property string $service_name 服务名字，如name1/name2等，空字符串表示默认
 * @property int $provider_id 关联ai_providers.id
 * @property int $is_active 是否启用:1启用,2禁用
 * @property string|null $description 映射说明
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property \Illuminate\Support\Carbon|null $deleted_at 软删除时间
 *
 * @property-read AiProvider|null $provider 关联的AI服务提供商
 */
class AiServiceMapping extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * 表名.
     */
    protected $table = 'ai_service_mappings';

    /**
     * 可批量赋值的字段.
     */
    protected $fillable = [
        'service_type',
        'service_name',
        'provider_id',
        'is_active',
        'description',
    ];

    /**
     * 字段类型转换.
     */
    protected $casts = [
        'is_active' => 'integer',
        'provider_id' => 'integer',
    ];

    /**
     * 设置 service_name 字段（自动处理 null 值）.
     *
     * @param string|null $value 服务名字
     */
    public function setServiceNameAttribute(?string $value): void
    {
        $this->attributes['service_name'] = $value ?? '';
    }

    /**
     * 关联：映射所属的服务提供商.
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(AiProvider::class, 'provider_id', 'id');
    }
}
