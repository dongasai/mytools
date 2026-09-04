<?php

namespace Modules\FeatureSms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 短信验证码模型
 *
 * field start
 *
 * @property int $id
 * @property string $mobile
 * @property string $token
 * @property string $type
 * @property string $code_value
 * @property \Carbon\Carbon $sent_at 发送时间
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at 删除时间
 *                                      field end
 */
class SmsCode extends Model
{
    use SoftDeletes;

    /**
     * 数据表名称
     *
     * @var string
     */
    protected $table = 'fsms_code';

    // attrlist start
    protected $fillable = [
        'id',
        'mobile',
        'token',
        'type',
        'code_value',
        'sent_at',
    ];
    // attrlist end

    /**
     * 应该被调整为日期的属性
     *
     * @var array
     */
    protected $dates = [
        'sent_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
