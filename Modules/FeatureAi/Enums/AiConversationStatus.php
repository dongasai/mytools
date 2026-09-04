<?php

namespace Modules\FeatureAi\Enums;

use DLaravel\Enum\EnumCore;
use DLaravel\Enum\EnumExpression;
use DLaravel\Enum\EnumToInt;
use Illuminate\Contracts\Database\Query\Expression;

/**
 * AI对话状态.
 */
enum AiConversationStatus: int implements Expression
{
    use EnumCore, EnumExpression, EnumToInt;

    /**
     * 进行中.
     */
    case ACTIVE = 1;

    /**
     * 已完成.
     */
    case COMPLETED = 2;

    /**
     * 失败.
     */
    case FAILED = 3;

    /**
     * 获取状态名称.
     */
    public function getName(): string
    {
        return match ($this) {
            self::ACTIVE => '进行中',
            self::COMPLETED => '已完成',
            self::FAILED => '失败',
        };
    }
}