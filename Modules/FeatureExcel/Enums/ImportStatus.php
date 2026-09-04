<?php

namespace Modules\FeatureExcel\Enums;

use DLaravel\Enum\EnumCore;

/**
 * 导入状态枚举
 */
enum ImportStatus: int
{
    use EnumCore;

    /** 待处理 */
    case PENDING = 0;

    /** 成功 */
    case SUCCESS = 1;

    /** 失败 */
    case FAILED = 2;

    /**
     * 获取所有状态
     */
    public static function getAll(): array
    {
        return [
            self::PENDING->value => '待处理',
            self::SUCCESS->value => '成功',
            self::FAILED->value => '失败',
        ];
    }
}
