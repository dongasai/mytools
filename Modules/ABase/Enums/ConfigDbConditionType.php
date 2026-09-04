<?php

namespace Modules\ABase\Enums;

/**
 * 配置表备份条件类型枚举
 */
enum ConfigDbConditionType: string
{
    case EQUAL = '=';
    case NOT_EQUAL = '!=';
    case GREATER_THAN = '>';
    case GREATER_THAN_OR_EQUAL = '>=';
    case LESS_THAN = '<';
    case LESS_THAN_OR_EQUAL = '<=';
    case LIKE = 'like';
    case IN = 'in';
    case NOT_IN = 'not in';
    case IS_NULL = 'is null';
    case IS_NOT_NULL = 'is not null';
    case BETWEEN = 'between';

    /**
     * 获取操作符描述
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::EQUAL => '等于',
            self::NOT_EQUAL => '不等于',
            self::GREATER_THAN => '大于',
            self::GREATER_THAN_OR_EQUAL => '大于等于',
            self::LESS_THAN => '小于',
            self::LESS_THAN_OR_EQUAL => '小于等于',
            self::LIKE => '模糊匹配',
            self::IN => '包含于',
            self::NOT_IN => '不包含于',
            self::IS_NULL => '为空',
            self::IS_NOT_NULL => '不为空',
            self::BETWEEN => '范围之间',
        };
    }

    /**
     * 获取操作符示例
     */
    public function getExample(): string
    {
        return match ($this) {
            self::EQUAL => "'key' => 'value'",
            self::NOT_EQUAL => "'status' => '!=' . 'inactive'",
            self::GREATER_THAN => "'price' => '>' . 100",
            self::GREATER_THAN_OR_EQUAL => "'created_at' => '>=' . '2024-01-01'",
            self::LESS_THAN => "'stock' => '<' . 10",
            self::LESS_THAN_OR_EQUAL => "'level' => '<=' . 5",
            self::LIKE => "'name' => 'like' . 'product_%'",
            self::IN => "'status' => ['in' => ['active', 'pending']]",
            self::NOT_IN => "'type' => ['not in' => ['temp', 'test']]",
            self::IS_NULL => "'deleted_at' => null",
            self::IS_NOT_NULL => "'updated_at' => ['not in' => [null]]",
            self::BETWEEN => "'date' => ['between' => ['2024-01-01', '2024-12-31']]",
        };
    }

    /**
     * 是否需要数组语法
     */
    public function requiresArraySyntax(): bool
    {
        return in_array($this, [
            self::IN,
            self::NOT_IN,
            self::BETWEEN,
        ]);
    }

    /**
     * 获取所有操作符
     */
    public static function getAllOperators(): array
    {
        return array_map(fn($case) => [
            'operator' => $case->value,
            'description' => $case->getDescription(),
            'example' => $case->getExample(),
            'requires_array' => $case->requiresArraySyntax(),
        ], self::cases());
    }
}
