<?php

namespace DLaravel\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * 队列运行情况
 *
 * field start
 *
 * @property int $id
 * @property string $queue
 * @property string $payload
 * @property string $runclass 运行类
 * @property int $attempts
 * @property int $reserved_at
 * @property int $available_at
 * @property \Carbon\Carbon $created_at
 * @property string $status 运行状态
 * @property string $desc 描述信息
 * @property float $runtime 运行时间
 *                          field end
 */
class JobRun extends Model
{
    public $timestamps = false;

    // attrlist start
    public static $attrlist = [
        0 => 'id',
        1 => 'queue',
        2 => 'payload',
        3 => 'runclass',
        4 => 'attempts',
        5 => 'reserved_at',
        6 => 'available_at',
        7 => 'created_at',
        8 => 'status',
        9 => 'desc',
        10 => 'runtime',
    ];
    // attrlist end

    protected $casts = [

        'payload' => 'array',
    ];
}
