<?php

namespace Modules\ABase\Enums;

/**
 * 配置表备份模式枚举
 */
enum ConfigDbBackupMode: string
{
    case FULL = 'full';
    case STRUCTURE_ONLY = 'structure_only';
    case DATA_ONLY = 'data_only';

    /**
     * 获取模式描述
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::FULL => '完整备份 - 包含表结构和数据',
            self::STRUCTURE_ONLY => '仅结构 - 只备份表创建语句',
            self::DATA_ONLY => '仅数据 - 只备份数据插入语句',
        };
    }

    /**
     * 获取模式图标
     */
    public function getIcon(): string
    {
        return match ($this) {
            self::FULL => '📋',
            self::STRUCTURE_ONLY => '🏗️',
            self::DATA_ONLY => '📊',
        };
    }

    /**
     * 是否包含表结构
     */
    public function includesStructure(): bool
    {
        return match ($this) {
            self::FULL,
            self::STRUCTURE_ONLY => true,
            self::DATA_ONLY => false,
        };
    }

    /**
     * 是否包含数据
     */
    public function includesData(): bool
    {
        return match ($this) {
            self::FULL,
            self::DATA_ONLY => true,
            self::STRUCTURE_ONLY => false,
        };
    }

    /**
     * 获取文件后缀
     */
    public function getFileSuffix(): string
    {
        return match ($this) {
            self::FULL => '',
            self::STRUCTURE_ONLY => '_structure',
            self::DATA_ONLY => '_data',
        };
    }

    /**
     * 验证模式是否有效
     */
    public static function isValid(string $mode): bool
    {
        return in_array($mode, array_column(self::cases(), 'value'));
    }

    /**
     * 获取所有可用模式
     */
    public static function getAllModes(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'description' => $case->getDescription(),
            'icon' => $case->getIcon(),
            'includes_structure' => $case->includesStructure(),
            'includes_data' => $case->includesData(),
            'file_suffix' => $case->getFileSuffix(),
        ], self::cases());
    }
}
