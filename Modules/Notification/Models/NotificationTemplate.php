<?php

declare(strict_types=1);

namespace Modules\Notification\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 通知模板模型.
 *
 * 用于管理各类通知的模板，支持多渠道模板配置和变量替换。
 *
 * @property int $id 主键ID
 * @property string $name 模板名称
 * @property string $notification_type 通知类型
 * @property string $channel 适用渠道
 * @property string|null $subject 通知标题
 * @property string $content 通知内容模板
 * @property array|null $variables 模板变量说明
 * @property bool $is_active 是否启用
 * @property string|null $description 模板描述
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 */
class NotificationTemplate extends Model
{
    /**
     * 表名.
     *
     * @var string
     */
    protected $table = 'notification_templates';

    /**
     * 可批量赋值的字段.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'notification_type',
        'channel',
        'subject',
        'content',
        'variables',
        'is_active',
        'description',
    ];

    /**
     * 字段类型转换.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];
}