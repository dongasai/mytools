<?php

namespace Modules\AClean\Enums;

/**
 * 数据分类枚举
 *
 * 定义了5种数据分类，用于自动识别表的数据类型
 */
enum DATA_CATEGORY: int
{
    /**
     * 用户运行数据
     * 包括：用户农场数据、物品数据、宠物数据等
     */
    case USER_DATA = 1;

    /**
     * 日志数据
     * 包括：操作日志、交易日志、系统日志等
     */
    case LOG_DATA = 2;

    /**
     * 交易数据
     * 包括：订单数据、交易记录、支付记录等
     */
    case TRANSACTION_DATA = 3;

    /**
     * 缓存数据
     * 包括：临时数据、会话数据、缓存表等
     */
    case CACHE_DATA = 4;

    /**
     * 配置数据
     * 包括：系统配置、模块配置、规则配置等
     * 注意：配置数据通常不应该被清理
     */
    case CONFIG_DATA = 5;

    /**
     * 获取数据分类的描述
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::USER_DATA => '用户运行数据',
            self::LOG_DATA => '日志数据',
            self::TRANSACTION_DATA => '交易数据',
            self::CACHE_DATA => '缓存数据',
            self::CONFIG_DATA => '配置数据',
        };
    }

    /**
     * 获取数据分类的详细说明
     */
    public function getDetailDescription(): string
    {
        return match ($this) {
            self::USER_DATA => '用户的运行数据，如农场数据、物品数据、宠物数据等',
            self::LOG_DATA => '各种日志数据，如操作日志、交易日志、系统日志等',
            self::TRANSACTION_DATA => '交易相关数据，如订单、支付记录、交易记录等',
            self::CACHE_DATA => '临时缓存数据，如会话数据、临时表、缓存表等',
            self::CONFIG_DATA => '系统配置数据，通常不应该被清理',
        };
    }

    /**
     * 获取数据分类的颜色标识
     */
    public function getColor(): string
    {
        return match ($this) {
            self::USER_DATA => 'primary',
            self::LOG_DATA => 'info',
            self::TRANSACTION_DATA => 'warning',
            self::CACHE_DATA => 'secondary',
            self::CONFIG_DATA => 'danger',
        };
    }

    /**
     * 判断是否默认启用清理
     */
    public function isDefaultEnabled(): bool
    {
        return match ($this) {
            self::USER_DATA => false,  // 用户数据需要谨慎处理
            self::LOG_DATA => true,    // 日志数据可以清理
            self::TRANSACTION_DATA => false,  // 交易数据需要谨慎处理
            self::CACHE_DATA => true,  // 缓存数据可以清理
            self::CONFIG_DATA => false,  // 配置数据不应该清理
        };
    }

    /**
     * 获取默认的清理类型
     */
    public function getDefaultCleanupType(): CLEANUP_TYPE
    {
        return match ($this) {
            self::USER_DATA => CLEANUP_TYPE::DELETE_BY_TIME,
            self::LOG_DATA => CLEANUP_TYPE::DELETE_BY_TIME,
            self::TRANSACTION_DATA => CLEANUP_TYPE::DELETE_BY_TIME,
            self::CACHE_DATA => CLEANUP_TYPE::TRUNCATE,
            self::CONFIG_DATA => CLEANUP_TYPE::DELETE_BY_CONDITION,  // 不建议清理
        };
    }

    /**
     * 获取默认的优先级
     */
    public function getDefaultPriority(): int
    {
        return match ($this) {
            self::USER_DATA => 100,
            self::LOG_DATA => 200,
            self::TRANSACTION_DATA => 150,
            self::CACHE_DATA => 400,
            self::CONFIG_DATA => 999,  // 最低优先级
        };
    }

    /**
     * 获取所有数据分类的选项数组
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
     * 获取带详细信息的选项数组
     */
    public static function getDetailOptions(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = [
                'name' => $case->getDescription(),
                'description' => $case->getDetailDescription(),
                'color' => $case->getColor(),
                'default_enabled' => $case->isDefaultEnabled(),
                'default_cleanup_type' => $case->getDefaultCleanupType()->value,
                'default_priority' => $case->getDefaultPriority(),
            ];
        }

        return $options;
    }

    /**
     * 根据表名自动识别数据分类
     */
    public static function detectFromTableName(string $tableName): self
    {
        $tableName = strtolower($tableName);

        // 移除表前缀
        $tableName = preg_replace('/^/', '', $tableName);

        // 配置数据识别
        if (str_contains($tableName, 'config') ||
            str_contains($tableName, 'setting') ||
            str_contains($tableName, 'rule') ||
            str_ends_with($tableName, '_configs') ||
            str_ends_with($tableName, '_settings') ||
            str_ends_with($tableName, '_rules')) {
            return self::CONFIG_DATA;
        }

        // 日志数据识别
        if (str_contains($tableName, 'log') ||
            str_contains($tableName, 'history') ||
            str_ends_with($tableName, '_logs') ||
            str_ends_with($tableName, '_histories')) {
            return self::LOG_DATA;
        }

        // 交易数据识别
        if (str_contains($tableName, 'order') ||
            str_contains($tableName, 'transaction') ||
            str_contains($tableName, 'payment') ||
            str_contains($tableName, 'trade') ||
            str_ends_with($tableName, '_orders') ||
            str_ends_with($tableName, '_transactions')) {
            return self::TRANSACTION_DATA;
        }

        // 缓存数据识别
        if (str_contains($tableName, 'cache') ||
            str_contains($tableName, 'session') ||
            str_contains($tableName, 'temp') ||
            str_starts_with($tableName, 'cache_') ||
            str_ends_with($tableName, '_cache')) {
            return self::CACHE_DATA;
        }

        // 默认为用户数据
        return self::USER_DATA;
    }
}
