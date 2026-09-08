<?php

namespace Modules\AClean\Enums;

/**
 * 清理类型枚举
 *
 * 定义了5种不同的清理类型，每种类型适用于不同的场景
 */
enum CLEANUP_TYPE: int
{
    /**
     * 清空表 - 使用 TRUNCATE 语句
     * 适用场景：缓存数据、临时数据
     * 特点：最快速，重置自增ID，不可回滚
     */
    case TRUNCATE = 1;

    /**
     * 删除所有记录 - 使用 DELETE 语句删除所有记录
     * 适用场景：需要保留自增ID的场景
     * 特点：保留自增ID，可回滚，触发触发器
     */
    case DELETE_ALL = 2;

    /**
     * 按时间删除 - 根据时间字段删除记录
     * 适用场景：有时间字段的表，保留最近数据
     * 特点：可设置保留天数，支持相对时间
     */
    case DELETE_BY_TIME = 3;

    /**
     * 按用户删除 - 根据用户字段删除记录
     * 适用场景：用户相关数据，清理特定用户
     * 特点：精确控制用户范围，支持用户列表
     */
    case DELETE_BY_USER = 4;

    /**
     * 按条件删除 - 根据自定义条件删除记录
     * 适用场景：复杂删除条件，自定义SQL条件
     * 特点：最大灵活性，支持复杂组合条件
     */
    case DELETE_BY_CONDITION = 5;

    /**
     * 获取清理类型的描述
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::TRUNCATE => '清空表',
            self::DELETE_ALL => '删除所有记录',
            self::DELETE_BY_TIME => '按时间删除',
            self::DELETE_BY_USER => '按用户删除',
            self::DELETE_BY_CONDITION => '按条件删除',
        };
    }

    /**
     * 获取清理类型的详细说明
     */
    public function getDetailDescription(): string
    {
        return match ($this) {
            self::TRUNCATE => '使用TRUNCATE语句清空表，最快速但不可回滚，会重置自增ID',
            self::DELETE_ALL => '使用DELETE语句删除所有记录，保留自增ID，可回滚',
            self::DELETE_BY_TIME => '根据时间字段删除记录，可设置保留天数',
            self::DELETE_BY_USER => '根据用户字段删除特定用户的记录',
            self::DELETE_BY_CONDITION => '根据自定义SQL条件删除记录，支持复杂条件',
        };
    }

    /**
     * 判断是否需要条件配置
     */
    public function needsConditions(): bool
    {
        return match ($this) {
            self::TRUNCATE, self::DELETE_ALL => false,
            self::DELETE_BY_TIME, self::DELETE_BY_USER, self::DELETE_BY_CONDITION => true,
        };
    }

    /**
     * 判断是否支持回滚
     */
    public function isRollbackable(): bool
    {
        return match ($this) {
            self::TRUNCATE => false,
            self::DELETE_ALL, self::DELETE_BY_TIME, self::DELETE_BY_USER, self::DELETE_BY_CONDITION => true,
        };
    }

    /**
     * 获取所有清理类型的选项数组
     */
    public static function getOptions(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->getDescription();
        }

        return $options;
    }

    /**
     * 获取带详细说明的选项数组
     */
    public static function getDetailOptions(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = [
                'name' => $case->getDescription(),
                'description' => $case->getDetailDescription(),
                'needs_conditions' => $case->needsConditions(),
                'rollbackable' => $case->isRollbackable(),
            ];
        }

        return $options;
    }
}
