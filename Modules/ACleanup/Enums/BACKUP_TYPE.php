<?php

namespace Modules\AClean\Enums;

/**
 * 备份类型枚举
 */
enum BACKUP_TYPE: int
{
    case SQL = 1;   // SQL文件
    case JSON = 2;  // JSON文件
    case CSV = 3;   // CSV文件

    /**
     * 获取类型名称
     */
    public function label(): string
    {
        return match ($this) {
            self::SQL => 'SQL',
            self::JSON => 'JSON',
            self::CSV => 'CSV',
        };
    }

    /**
     * 获取所有选项（用于 Dcat Admin select）
     */
    public static function getSelectOptions(): array
    {
        return [
            self::SQL->value => self::SQL->label(),
            self::JSON->value => self::JSON->label(),
            self::CSV->value => self::CSV->label(),
        ];
    }

    /**
     * 获取所有选项（别名方法，用于兼容）
     */
    public static function getOptions(): array
    {
        return self::getSelectOptions();
    }
}