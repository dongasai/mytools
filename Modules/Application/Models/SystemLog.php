<?php

namespace Modules\Application\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 队列运行记录模型
 *
 * field start
 *
 * @property int $id 主键ID
 * @property string $level1 来源类型（fund, item, farm等）
 * @property string $message 日志消息内容
 * @property \Carbon\Carbon $created_at 创建时间（兼容字段，等同于collected_at）
 * @property string $data1 数据
 *                         field end
 */
class SystemLog extends Model
{
    protected $table = 'application_system_logs';

    public $timestamps = false;

    // attrlist start
    protected $fillable = [
        'id',
        'level1',
        'message',
        'data1',
    ];
    // attrlist end

}
