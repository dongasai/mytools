<?php

namespace Modules\Application\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 连续次数判定
 *
 * field start
 *
 * @property int $id 主键id
 * @property int $user_id 用户id
 * @property string $stype 产品类型
 * @property int $sid 产品id
 * @property int $number 计数
 * @property int $last_time 最后的时间
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property int $diff
 *                     field end
 */
class ContinuousTimes extends Model
{
    protected $table = 'application_continuous_times';
    // attrlist start
    protected $fillable = [
        'id',
        'user_id',
        'stype',
        'sid',
        'number',
        'last_time',
        'diff',
    ];
    // attrlist end
}
