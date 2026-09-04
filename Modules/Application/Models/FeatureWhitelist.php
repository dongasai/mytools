<?php

namespace Modules\Application\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modules\Application\Models\FeatureWhitelist
 *
 * 功能白名单模型
 *
 * field start
 *
 * @property int $id
 * @property int $feature_id 功能ID
 * @property int $user_id 用户ID
 * @property string $reason 加入白名单原因
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * field end
 */
class FeatureWhitelist extends Model
{
    use HasDateTimeFormatter;

    /**
     * 表名
     *
     * @var string
     */
    protected $table = 'feature_whitelist';

    /**
     * 可填充字段
     *
     * @var array
     */
    protected $fillable = [
        'feature_id',
        'user_id',
        'reason',
    ];

    /**
     * 获取所属功能
     *
     * @return BelongsTo
     */
    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class, 'feature_id', 'id');
    }

    /**
     * 获取所属用户
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\Modules\User\Models\User::class, 'user_id', 'id');
    }
}