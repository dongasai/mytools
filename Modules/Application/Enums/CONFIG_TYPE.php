<?php

namespace Modules\Application\Enums;

use DLaravel\Enum\EnumCore;
use DLaravel\Enum\EnumToInt;

/**
 * 配置类型
 */
enum CONFIG_TYPE: int
{
    use EnumCore,EnumToInt;

    /**
     * 数字类型
     */
    case TYPE_INT = 1;

    /**
     * 图片类型
     */
    case TYPE_IMG = 2;

    /**
     * bool类型(开关)
     */
    case TYPE_BOOL = 3;

    /**
     * 字符串类型
     */
    case TYPE_STRING = 4;

    /**
     * 浮点型
     */
    case TYPE_FLOAT = 5;

    /**
     * 文件类型
     */
    case TYPE_FILE = 6;

    /**
     * 百分比(小数20,10)
     */
    case TYPE_PERCENTAGE = 7;

    /**
     * 时间,秒
     */
    case TYPE_TIME = 8;

    /**
     * 布尔(是否)
     */
    case TYPE_IS = 9;

    /**
     * json-Array
     */
    case TYPE_JSON = 10;

    /**
     * json-固定键值对
     */
    case TYPE_EMBEDS = 11;

}
