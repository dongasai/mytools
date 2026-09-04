<?php

namespace Modules\ABase\Enums;

/**
 * 配置表备份类型枚举
 */
enum ConfigDbType: string
{
    case MODEL = 'model';
    case PREFIX = 'prefix';

    /**
     * 获取类型描述
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::MODEL => '模型类型 - 通过模型类精确指定表',
            self::PREFIX => '前缀类型 - 通过表前缀批量指定表',
        };
    }

    /**
     * 获取类型图标
     */
    public function getIcon(): string
    {
        return match ($this) {
            self::MODEL => '📄',
            self::PREFIX => '📦',
        };
    }

    /**
     * 验证类型是否有效
     */
    public static function isValid(string $type): bool
    {
        return in_array($type, array_column(self::cases(), 'value'));
    }

    /**
     * 获取所有可用类型
     */
    public static function getAllTypes(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'description' => $case->getDescription(),
            'icon' => $case->getIcon(),
        ], self::cases());
    }
}
