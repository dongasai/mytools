<?php

namespace Modules\AFile\Enums;

use DLaravel\Enum\EnumCore;
use DLaravel\Enum\EnumToInt;

/**
 * 文件可见性枚举
 */
enum FILE_VISIBILITY: int
{
    use EnumCore, EnumToInt;

    /**
     * 公开
     */
    case PUBLIC = 0;

    /**
     * 私有
     */
    case PRIVATE = 1;

    /**
     * 获取所有可见性
     */
    public static function getAll(): array
    {
        return [
            self::PUBLIC->value => '公开',
            self::PRIVATE->value => '私有',
        ];
    }
}
