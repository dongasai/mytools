<?php

declare(strict_types=1);

namespace Modules\Notification\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * 通知日志模型.
 *
 * 用于记录所有通知发送的历史记录，包括发送状态、渠道、内容等信息。
 *
 * @property int $id 主键ID
 * @property string $notification_type 通知类型
 * @property string $channel 通知渠道
 * @property string $notifiable_type 接收对象类型
 * @property int $notifiable_id 接收对象ID
 * @property string $status 发送状态
 * @property array|null $data 通知数据
 * @property string|null $error_message 错误信息
 * @property \Illuminate\Support\Carbon|null $sent_at 发送时间
 * @property int $retry_count 重试次数
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property \Illuminate\Support\Carbon|null $deleted_at 软删除时间
 * @property Model $notifiable 多态关联的接收对象
 */
class NotificationLog extends Model
{
    use SoftDeletes;

    /**
     * 表名.
     *
     * @var string
     */
    protected $table = 'notification_logs';

    /**
     * 可批量赋值的字段.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'notification_type',
        'channel',
        'notifiable_type',
        'notifiable_id',
        'status',
        'data',
        'error_message',
        'sent_at',
        'retry_count',
    ];

    /**
     * 字段类型转换.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
        'sent_at' => 'datetime',
        'retry_count' => 'integer',
        'deleted_at' => 'datetime',
    ];

    /**
     * 获取接收通知的对象（多态关联）.
     *
     * @return MorphTo<Model, NotificationLog>
     */
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }
}