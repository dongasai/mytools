<?php

namespace Modules\FeatureSms\Enums;

/**
 * 短信类型枚举
 */
class Code
{
    public static $mapMessage = [
        CODE_TYPE::REGISTER->value => \Modules\FeatureSms\Dtos\RegisterMessage::class,
        CODE_TYPE::LOGIN->value => \Modules\FeatureSms\Dtos\LoginMessage::class,
        CODE_TYPE::RESET_PASSWORD->value => \Modules\FeatureSms\Dtos\ResetPasswordMessage::class,
    ];

    /**
     * 获取所有类型
     */
    public static function getAll(): array
    {
        return [
            CODE_TYPE::REGISTER->value => '注册验证码',
            CODE_TYPE::LOGIN->value => '登录验证码',
            CODE_TYPE::RESET_PASSWORD->value => '重置密码验证码',
        ];
    }

    /**
     * 获取类型对应的消息模板类
     *
     * @param  CODE_TYPE  $type  类型值
     */
    public static function getTemplateClass(CODE_TYPE $type): ?string
    {
        return self::$mapMessage[$type->value] ?? null;
    }

    /**
     * 验证类型是否有效
     *
     * @param  CODE_TYPE  $type  类型值
     */
    public static function isValid(CODE_TYPE $type): bool
    {
        return isset(self::getAll()[$type->value]);
    }
}
