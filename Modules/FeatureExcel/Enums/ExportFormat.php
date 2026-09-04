<?php

namespace Modules\FeatureExcel\Enums;

use DLaravel\Enum\EnumCore;

/**
 * 导出格式枚举
 */
enum ExportFormat: string
{
    use EnumCore;

    /** Excel格式 */
    case EXCEL = 'excel';

    /** CSV格式 */
    case CSV = 'csv';

    /**
     * 获取所有选项
     */
    public static function getAll(): array
    {
        return [
            self::EXCEL->value => 'Excel',
            self::CSV->value => 'CSV',
        ];
    }
}
