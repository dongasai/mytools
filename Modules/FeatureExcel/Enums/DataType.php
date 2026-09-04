<?php

namespace Modules\FeatureExcel\Enums;

use DLaravel\Enum\EnumCore;

/**
 * 数据类型枚举
 */
enum DataType: string
{
    use EnumCore;

    /** 整数 */
    case INTEGER = 'integer';

    /** 浮点数 */
    case FLOAT = 'float';

    /** 字符串 */
    case STRING = 'string';

    /** 日期 */
    case DATE = 'date';

    /** 日期时间 */
    case DATETIME = 'datetime';

    /** 布尔值 */
    case BOOLEAN = 'boolean';

    /**
     * 获取所有类型
     */
    public static function getAll(): array
    {
        return [
            self::INTEGER->value => '整数',
            self::FLOAT->value => '浮点数',
            self::STRING->value => '字符串',
            self::DATE->value => '日期',
            self::DATETIME->value => '日期时间',
            self::BOOLEAN->value => '布尔值',
        ];
    }
}
