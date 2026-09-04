<?php

namespace Modules\FeatureSms\Enums;

enum CODE_TYPE: int
{
    /**
     * 注册验证码
     */
    case REGISTER = 1;

    /**
     * 登录验证码
     */
    case LOGIN = 2;

    /**
     * 重置密码验证码
     */
    case RESET_PASSWORD = 3;

    /**
     * 获取所有标签
     */
    public static function getLabels(): array
    {
        return [
            self::REGISTER->value => '注册验证码',
            self::LOGIN->value => '登录验证码',
            self::RESET_PASSWORD->value => '重置密码验证码',
        ];
    }
}
