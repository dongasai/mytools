<?php

namespace Modules\ABase\Enums;

/**
 * 配置表备份验证规则枚举
 */
enum ConfigDbValidationRule: string
{
    case REQUIRED = 'required';
    case STRING = 'string';
    case INTEGER = 'integer';
    case BOOLEAN = 'boolean';
    case ARRAY = 'array';
    case EMAIL = 'email';
    case URL = 'url';
    case DATE = 'date';
    case DATETIME = 'datetime';
    case JSON = 'json';
    case REGEX = 'regex';

    /**
     * 获取验证规则描述
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::REQUIRED => '必填字段',
            self::STRING => '字符串类型',
            self::INTEGER => '整数类型',
            self::BOOLEAN => '布尔类型',
            self::ARRAY => '数组类型',
            self::EMAIL => '邮箱格式',
            self::URL => 'URL格式',
            self::DATE => '日期格式',
            self::DATETIME => '日期时间格式',
            self::JSON => 'JSON格式',
            self::REGEX => '正则表达式',
        };
    }

    /**
     * 获取Laravel验证规则
     */
    public function getLaravelRule(): string
    {
        return match ($this) {
            self::REQUIRED => 'required',
            self::STRING => 'string',
            self::INTEGER => 'integer',
            self::BOOLEAN => 'boolean',
            self::ARRAY => 'array',
            self::EMAIL => 'email',
            self::URL => 'url',
            self::DATE => 'date',
            self::DATETIME => 'date',
            self::JSON => 'json',
            self::REGEX => 'regex:/pattern/',
        };
    }

    /**
     * 获取验证图标
     */
    public function getIcon(): string
    {
        return match ($this) {
            self::REQUIRED => '✅',
            self::STRING => '📝',
            self::INTEGER => '🔢',
            self::BOOLEAN => '🔘',
            self::ARRAY => '📋',
            self::EMAIL => '📧',
            self::URL => '🔗',
            self::DATE => '📅',
            self::DATETIME => '🕐',
            self::JSON => '📄',
            self::REGEX => '🔍',
        };
    }

    /**
     * 获取默认错误消息
     */
    public function getDefaultErrorMessage(): string
    {
        return match ($this) {
            self::REQUIRED => '此字段为必填项',
            self::STRING => '请输入有效的字符串',
            self::INTEGER => '请输入有效的整数',
            self::BOOLEAN => '请输入有效的布尔值',
            self::ARRAY => '请输入有效的数组',
            self::EMAIL => '请输入有效的邮箱地址',
            self::URL => '请输入有效的URL地址',
            self::DATE => '请输入有效的日期',
            self::DATETIME => '请输入有效的日期时间',
            self::JSON => '请输入有效的JSON格式',
            self::REGEX => '输入格式不正确',
        };
    }

    /**
     * 是否需要参数
     */
    public function requiresParameter(): bool
    {
        return $this === self::REGEX;
    }

    /**
     * 获取验证示例
     */
    public function getExample(): string
    {
        return match ($this) {
            self::REQUIRED => 'field => required',
            self::STRING => 'name => string|max:255',
            self::INTEGER => 'age => integer|min:0|max:150',
            self::BOOLEAN => 'is_active => boolean',
            self::ARRAY => 'tags => array',
            self::EMAIL => 'email => email',
            self::URL => 'website => url',
            self::DATE => 'birthday => date',
            self::DATETIME => 'created_at => datetime',
            self::JSON => 'metadata => json',
            self::REGEX => 'phone => regex:/^1[3-9]\d{9}$/',
        };
    }

    /**
     * 验证规则是否有效
     */
    public static function isValid(string $rule): bool
    {
        return in_array($rule, array_column(self::cases(), 'value'));
    }

    /**
     * 获取所有验证规则
     */
    public static function getAllRules(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'description' => $case->getDescription(),
            'icon' => $case->getIcon(),
            'laravel_rule' => $case->getLaravelRule(),
            'requires_parameter' => $case->requiresParameter(),
            'example' => $case->getExample(),
            'error_message' => $case->getDefaultErrorMessage(),
        ], self::cases());
    }
}
