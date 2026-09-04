<?php

namespace Modules\AFile\Enums;

use DLaravel\Enum\EnumCore;
use DLaravel\Enum\EnumToInt;

/**
 * 文件状态枚举
 */
enum FILE_STATUS: int
{
    use EnumCore, EnumToInt;

    /**
     * 正常
     */
    case NORMAL = 1;

    /**
     * 已删除
     */
    case DELETED = 0;

    /**
     * 获取所有状态
     */
    public static function getAll(): array
    {
        return [
            self::NORMAL->value => '正常',
            self::DELETED->value => '已删除',
        ];
    }
}
