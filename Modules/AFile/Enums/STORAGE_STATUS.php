<?php

namespace Modules\AFile\Enums;

use DLaravel\Enum\EnumCore;
use DLaravel\Enum\EnumToInt;

/**
 * 存储状态枚举
 */
enum STORAGE_STATUS: int
{
    use EnumCore, EnumToInt;

    /**
     * 禁用
     */
    case DISABLED = 0;

    /**
     * 启用
     */
    case ENABLED = 1;

    /**
     * 获取所有状态
     */
    public static function getAll(): array
    {
        return [
            self::DISABLED->value => '禁用',
            self::ENABLED->value => '启用',
        ];
    }
}
