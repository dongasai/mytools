<?php

namespace Modules\FeatureSms\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 短信配置模型
 *
 * field start
 *
 * @property int $id
 * @property string $driver 驱动
 * @property bool $is_open 是否开启
 * @property string $title 标题
 * @property string $desc 描述
 * @property int $type 类型 详情见枚举
 * @property string $value 值
 * @property string $group 分组
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at 删除时间
 *                                      field end
 */
class SmsConfig extends Model
{
    /**
     * 数据表名称
     *
     * @var string
     */
    protected $table = 'fsms_config';

    // attrlist start
    protected $fillable = [
        'id',
        'driver',
        'is_open',
        'title',
        'desc',
        'type',
        'value',
        'group',
    ];
    // attrlist end

    /**
     * 应该被调整为日期的属性
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * 属性类型转换
     *
     * @var array
     */
    protected $casts = [
        'type' => 'integer',
        'is_open' => 'boolean',
    ];
}
