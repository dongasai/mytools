<?php

namespace Modules\AClean\Enums;

/**
 * 压缩类型枚举
 */
enum COMPRESSION_TYPE: int
{
    case NONE = 1;  // 无压缩
    case GZIP = 2;  // Gzip压缩
    case ZIP = 3;   // Zip压缩

    /**
     * 获取类型名称
     */
    public function label(): string
    {
        return match ($this) {
            self::NONE => '无压缩',
            self::GZIP => 'Gzip',
            self::ZIP => 'Zip',
        };
    }

    /**
     * 获取所有选项（用于 Dcat Admin select）
     */
    public static function getSelectOptions(): array
    {
        return [
            self::NONE->value => self::NONE->label(),
            self::GZIP->value => self::GZIP->label(),
            self::ZIP->value => self::ZIP->label(),
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