<?php

declare(strict_types=1);

namespace Modules\Notification\Enums;

/**
 * 通知状态枚举.
 *
 * 定义通知发送的所有可能状态。
 */
enum NOTIFICATION_STATUS: string
{
    /** 待发送 */
    case PENDING = 'pending';

    /** 发送中 */
    case SENDING = 'sending';

    /** 已发送 */
    case SENT = 'sent';

    /** 发送失败 */
    case FAILED = 'failed';
}