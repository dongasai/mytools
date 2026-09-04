<?php

namespace Modules\FeatureAi\Enums;

use DLaravel\Enum\EnumCore;
use DLaravel\Enum\EnumExpression;
use DLaravel\Enum\EnumToInt;
use Illuminate\Contracts\Database\Query\Expression;

/**
 * AI图片生成状态.
 */
enum AiImageStatus: int implements Expression
{
    use EnumCore, EnumExpression, EnumToInt;

    /**
     * 待处理.
     */
    case PENDING = 1;

    /**
     * 处理中.
     */
    case PROCESSING = 2;

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
            self::PENDING => '待处理',
            self::PROCESSING => '处理中',
            self::SUCCESS => '成功',
            self::FAILED => '失败',
        };
    }
}