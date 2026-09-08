<?php

namespace Modules\AClean\Enums;

/**
 * 批次状态枚举
 */
enum BATCH_STATUS: int
{
    case PENDING = 0;      // 等待中
    case IN_PROGRESS = 1;  // 执行中
    case COMPLETED = 2;    // 已完成
    case FAILED = 3;       // 失败
    case CANCELLED = 4;    // 已取消

    /**
     * 获取状态名称
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => '等待中',
            self::IN_PROGRESS => '执行中',
            self::COMPLETED => '已完成',
            self::FAILED => '失败',
            self::CANCELLED => '已取消',
        };
    }

    /**
     * 获取状态图标
     */
    public function icon(): string
    {
        return match ($this) {
            self::PENDING => '⏳',
            self::IN_PROGRESS => '🔄',
            self::COMPLETED => '✅',
            self::FAILED => '❌',
            self::CANCELLED => '🚫',
        };
    }

    /**
     * 获取CSS类名
     */
    public function cssClass(): string
    {
        return match ($this) {
            self::PENDING => 'badge-secondary',
            self::IN_PROGRESS => 'badge-primary',
            self::COMPLETED => 'badge-success',
            self::FAILED => 'badge-danger',
            self::CANCELLED => 'badge-warning',
        };
    }

    /**
     * 获取所有选项（用于 Dcat Admin select）
     */
    public static function getSelectOptions(): array
    {
        return [
            self::PENDING->value => self::PENDING->label(),
            self::IN_PROGRESS->value => self::IN_PROGRESS->label(),
            self::COMPLETED->value => self::COMPLETED->label(),
            self::FAILED->value => self::FAILED->label(),
            self::CANCELLED->value => self::CANCELLED->label(),
        ];
    }
}