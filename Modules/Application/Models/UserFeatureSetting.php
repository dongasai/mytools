<?php

namespace Modules\Application\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modules\Application\Models\UserFeatureSetting
 *
 * 用户功能开关配置模型
 *
 * field start
 *
 * @property int $id
 * @property int $user_id 用户ID
 * @property int $feature_id 功能ID
 * @property bool $is_enabled 开关状态:0关闭 1开启
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * field end
 */
class UserFeatureSetting extends Model
{
    use HasDateTimeFormatter;

    /**
     * 表名
     *
     * @var string
     */
    protected $table = 'user_feature_settings';

    /**
     * 可填充字段
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'feature_id',
        'is_enabled',
    ];

    /**
     * 字段类型转换
     *
     * @var array
     */
    protected $casts = [
        'is_enabled' => 'boolean',
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