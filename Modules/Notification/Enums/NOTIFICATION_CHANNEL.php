<?php

declare(strict_types=1);

namespace Modules\Notification\Enums;

/**
 * 通知渠道枚举.
 *
 * 定义支持的通知发送渠道类型。
 */
enum NOTIFICATION_CHANNEL: string
{
    /** 短信渠道 */
    case SMS = 'sms';

    /** 邮件渠道 */
    case MAIL = 'mail';

    /** 推送渠道 */
    case PUSH = 'push';
}