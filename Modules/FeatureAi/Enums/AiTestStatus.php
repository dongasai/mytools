<?php

namespace Modules\FeatureAi\Enums;

use DLaravel\Enum\EnumCore;
use DLaravel\Enum\EnumExpression;
use DLaravel\Enum\EnumToInt;
use Illuminate\Contracts\Database\Query\Expression;

/**
 * AI测试状态.
 */
enum AiTestStatus: int implements Expression
{
    use EnumCore, EnumExpression, EnumToInt;

    /**
     * 待执行.
     */
    case PENDING = 1;

    /**
     * 执行中.
     */
    case RUNNING = 2;

    /**
     * 成功.
     */
    case SUCCESS = 3;

    /**
     * 失败.
     */
    case FAILED = 4;

    /**
     * 获取状态名称.
     */
    public function getName(): string
    {
        return match ($this) {
            self::PENDING => '待执行',
            self::RUNNING => '执行中',
            self::SUCCESS => '成功',
            self::FAILED => '失败',
        };
    }
}