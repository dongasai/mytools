<?php

namespace Modules\FeatureSms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * db驱动-短信记录 模型
 *
 * field start
 *
 * @property int $id
 * @property string $tpl_id
 * @property string $tpl_value
 * @property string $key
 * @property string $universal_number
 * @property string $mobile
 * @property string $content
 * @property string $idd_code
 * @property string $zero_prefixed_number
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at 删除时间
 *                                      field end
 */
class SmsDbGateway extends Model
{
    use SoftDeletes;

    /**
     * 数据表名称
     *
     * @var string
     */
    protected $table = 'fsms_dbgateway';

    // attrlist start
    protected $fillable = [
        'id',
        'tpl_id',
        'tpl_value',
        'key',
        'universal_number',
        'mobile',
        'content',
        'idd_code',
        'zero_prefixed_number',
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
        'deleted_at',
    ];
}
