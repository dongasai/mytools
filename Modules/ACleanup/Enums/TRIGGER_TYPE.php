<?php

namespace Modules\AClean\Enums;

/**
 * 批次触发类型枚举
 */
enum TRIGGER_TYPE: string
{
    case MANUAL = 'manual';        // 手动触发
    case SCHEDULED = 'scheduled';  // 定时触发

    /**
     * 获取类型名称
     */
    public function label(): string
    {
        return match ($this) {
            self::MANUAL => '手动触发',
            self::SCHEDULED => '定时触发',
        };
    }

    /**
     * 获取类型图标
     */
    public function icon(): string
    {
        return match ($this) {
            self::MANUAL => '👤',
            self::SCHEDULED => '⏰',
        };
    }

    /**
     * 获取CSS类名
     */
    public function cssClass(): string
    {
        return match ($this) {
            self::MANUAL => 'badge-info',
            self::SCHEDULED => 'badge-warning',
        };
    }

    /**
     * 获取所有选项（用于 Dcat Admin select）
     */
    public static function getSelectOptions(): array
    {
        return [
            self::MANUAL->value => self::MANUAL->label(),
            self::SCHEDULED->value => self::SCHEDULED->label(),
        ];
    }
}