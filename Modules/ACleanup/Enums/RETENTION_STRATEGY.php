<?php

namespace Modules\AClean\Enums;

/**
 * 保留策略枚举
 *
 * 定义备份批次的保留策略
 */
enum RETENTION_STRATEGY: int
{
    case COUNT = 1;   // 按数量保留
    case TIME = 2;    // 按时间保留
    case HYBRID = 3;  // 混合策略

    /**
     * 获取策略名称
     */
    public function label(): string
    {
        return match ($this) {
            self::COUNT => '按数量保留',
            self::TIME => '按时间保留',
            self::HYBRID => '混合策略',
        };
    }

    /**
     * 获取策略描述
     */
    public function description(): string
    {
        return match ($this) {
            self::COUNT => '保留最近 N 个已完成的批次',
            self::TIME => '保留最近 N 天内完成的批次',
            self::HYBRID => '保留最近 N 个批次，但不超过 M 天',
        };
    }

    /**
     * 获取策略图标
     */
    public function icon(): string
    {
        return match ($this) {
            self::COUNT => '📊',
            self::TIME => '⏱️',
            self::HYBRID => '🔧',
        };
    }

    /**
     * 获取CSS类名
     */
    public function cssClass(): string
    {
        return match ($this) {
            self::COUNT => 'badge-info',
            self::TIME => 'badge-warning',
            self::HYBRID => 'badge-success',
        };
    }

    /**
     * 获取所有选项（用于 Dcat Admin select）
     */
    public static function getSelectOptions(): array
    {
        return [
            self::COUNT->value => self::COUNT->label(),
            self::TIME->value => self::TIME->label(),
            self::HYBRID->value => self::HYBRID->label(),
        ];
    }

    /**
     * 获取带描述的选项（用于表单帮助）
     */
    public static function getOptionsWithDescription(): array
    {
        return [
            self::COUNT->value => self::COUNT->label() . ' - ' . self::COUNT->description(),
            self::TIME->value => self::TIME->label() . ' - ' . self::TIME->description(),
            self::HYBRID->value => self::HYBRID->label() . ' - ' . self::HYBRID->description(),
        ];
    }
}