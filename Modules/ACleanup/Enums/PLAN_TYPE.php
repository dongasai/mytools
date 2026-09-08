<?php

namespace Modules\AClean\Enums;

/**
 * 计划类型枚举
 *
 * 定义了5种不同的计划类型，每种类型适用于不同的清理场景
 */
enum PLAN_TYPE: int
{
    /**
     * 全量清理 - 清理所有可清理的表
     * 适用场景：系统重置、大规模清理
     * 特点：最全面，需要谨慎使用
     */
    case FULL = 1;

    /**
     * 模块清理 - 清理指定模块的表
     * 适用场景：模块级别的清理，模块维护
     * 特点：按模块边界清理，精确控制
     */
    case MODULE = 2;

    /**
     * 分类清理 - 清理指定数据分类的表
     * 适用场景：日志清理、缓存清理等分类场景
     * 特点：按数据类型清理，自动识别
     */
    case CATEGORY = 3;

    /**
     * 自定义清理 - 用户自定义选择表
     * 适用场景：特定需求，精确表选择
     * 特点：最大灵活性，完全自定义
     */
    case CUSTOM = 4;

    /**
     * 混合清理 - 组合多种清理类型
     * 适用场景：复杂场景，多种类型组合
     * 特点：组合灵活性，满足复杂需求
     */
    case MIXED = 5;

    /**
     * 获取计划类型的描述
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::FULL => '全量清理',
            self::MODULE => '模块清理',
            self::CATEGORY => '分类清理',
            self::CUSTOM => '自定义清理',
            self::MIXED => '混合清理',
        };
    }

    /**
     * 获取计划类型的详细说明
     */
    public function getDetailDescription(): string
    {
        return match ($this) {
            self::FULL => '清理系统中所有可清理的表，最全面但需要谨慎使用',
            self::MODULE => '按模块边界清理指定模块的表，精确控制模块范围',
            self::CATEGORY => '按数据分类清理指定类型的表，自动识别数据类型',
            self::CUSTOM => '用户自定义选择需要清理的表，最大灵活性',
            self::MIXED => '组合多种清理类型，满足复杂清理需求',
        };
    }

    /**
     * 判断是否需要选择配置
     */
    public function needsSelection(): bool
    {
        return match ($this) {
            self::FULL => false,
            self::MODULE, self::CATEGORY, self::CUSTOM, self::MIXED => true,
        };
    }

    /**
     * 获取选择类型标识
     */
    public function getSelectionType(): string
    {
        return match ($this) {
            self::FULL => 'all',
            self::MODULE => 'module',
            self::CATEGORY => 'category',
            self::CUSTOM => 'custom',
            self::MIXED => 'mixed',
        };
    }

    /**
     * 获取所有计划类型的选项数组
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
                'needs_selection' => $case->needsSelection(),
                'selection_type' => $case->getSelectionType(),
            ];
        }

        return $options;
    }

    /**
     * 从字符串获取计划类型
     */
    public static function fromString(string $type): ?self
    {
        return match (strtolower($type)) {
            'full', 'all' => self::FULL,
            'module' => self::MODULE,
            'category' => self::CATEGORY,
            'custom' => self::CUSTOM,
            'mixed' => self::MIXED,
            default => null,
        };
    }
}