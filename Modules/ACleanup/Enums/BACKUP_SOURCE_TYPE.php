<?php

namespace Modules\AClean\Enums;

/**
 * 备份来源类型枚举
 *
 * 定义备份记录的来源类型，区分备份是由清理计划、独立备份计划还是手动创建
 */
enum BACKUP_SOURCE_TYPE: int
{
    /**
     * 清理计划触发的备份
     */
    case CLEANUP_PLAN = 1;

    /**
     * 独立备份计划触发的备份
     */
    case BACKUP_PLAN = 2;

    /**
     * 手动备份
     */
    case MANUAL = 3;

    /**
     * 获取备份来源类型的描述
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::CLEANUP_PLAN => '清理计划',
            self::BACKUP_PLAN => '独立备份计划',
            self::MANUAL => '手动备份',
        };
    }

    /**
     * 获取所有备份来源类型的选项数组
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
