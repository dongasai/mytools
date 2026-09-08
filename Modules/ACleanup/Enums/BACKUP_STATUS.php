<?php

namespace Modules\AClean\Enums;

/**
 * 备份状态枚举
 *
 * 定义备份记录的生命周期状态
 */
enum BACKUP_STATUS: int
{
    /**
     * 等待中
     */
    case PENDING = 0;

    /**
     * 进行中
     */
    case IN_PROGRESS = 1;

    /**
     * 已完成
     */
    case COMPLETED = 2;

    /**
     * 已失败
     */
    case FAILED = 3;

    /**
     * 已取消
     */
    case CANCELLED = 4;

    /**
     * 获取备份状态的描述
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::PENDING => '等待中',
            self::IN_PROGRESS => '进行中',
            self::COMPLETED => '已完成',
            self::FAILED => '已失败',
            self::CANCELLED => '已取消',
        };
    }

    /**
     * 获取状态对应的颜色
     */
    public function getColor(): string
    {
        return match ($this) {
            self::PENDING => 'secondary',
            self::IN_PROGRESS => 'primary',
            self::COMPLETED => 'success',
            self::FAILED => 'danger',
            self::CANCELLED => 'warning',
        };
    }

    /**
     * 获取状态对应的图标
     */
    public function getIcon(): string
    {
        return match ($this) {
            self::PENDING => 'fa-clock',
            self::IN_PROGRESS => 'fa-spinner',
            self::COMPLETED => 'fa-check-circle',
            self::FAILED => 'fa-times-circle',
            self::CANCELLED => 'fa-ban',
        };
    }

    /**
     * 获取所有备份状态的选项数组
     */
    public static function getOptions(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->getDescription();
        }

        return $options;
    }
}
