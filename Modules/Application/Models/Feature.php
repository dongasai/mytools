<?php

namespace Modules\Application\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modules\Application\Models\Feature
 *
 * 功能定义模型
 *
 * field start
 *
 * @property int $id 功能ID
 * @property string $name 功能名称
 * @property string $key 功能标识(唯一)
 * @property string $description 功能描述
 * @property bool $is_enabled 默认状态:0关闭 1开启
 * @property int $percentage 灰度百分比(0-100)
 * @property string $group 功能分组(创作类/社交类/AI类)
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * field end
 */
class Feature extends Model
{
    use HasDateTimeFormatter;

    /**
     * 表名
     *
     * @var string
     */
    protected $table = 'features';

    /**
     * 可填充字段
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'key',
        'description',
        'is_enabled',
        'percentage',
        'group',
    ];

    /**
     * 字段类型转换
     *
     * @var array
     */
    protected $casts = [
        'is_enabled' => 'boolean',
        'percentage' => 'integer',
    ];

    /**
     * 获取用户设置关联
     *
     * @return HasMany
     */
    public function userSettings(): HasMany
    {
        return $this->hasMany(UserFeatureSetting::class, 'feature_id', 'id');
    }

    /**
     * 获取白名单关联
     *
     * @return HasMany
     */
    public function whitelist(): HasMany
    {
        return $this->hasMany(FeatureWhitelist::class, 'feature_id', 'id');
    }

    /**
     * 获取黑名单关联
     *
     * @return HasMany
     */
    public function blacklist(): HasMany
    {
        return $this->hasMany(FeatureBlacklist::class, 'feature_id', 'id');
    }
}