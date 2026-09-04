<?php

use Modules\FeatureSms\Enums\CODE_TYPE;

return [
    /*
    |--------------------------------------------------------------------------
    | 短信配置
    |--------------------------------------------------------------------------
    |
    | 短信服务相关配置
    |
    */

    // 验证码有效期（秒）
    'code_expire_time' => 600,

    // 每日发送限制
    'daily_limit' => 10,

    // 发送间隔（秒）
    'send_interval' => 60,

    // 默认驱动
    'default_driver' => 'log',

    // 可用驱动
    'drivers' => [
        'log' => [
            'name' => '日志驱动',
            'description' => '将短信内容记录到日志中',
        ],
        'aliyun' => [
            'name' => '阿里云短信',
            'description' => '阿里云短信服务',
        ],
        'tencent' => [
            'name' => '腾讯云短信',
            'description' => '腾讯云短信服务',
        ],
    ],

    // 验证码模板
    'templates' => [
        CODE_TYPE::LOGIN->value => '您的登录验证码是：{code}，5分钟内有效。',
        CODE_TYPE::REGISTER->value => '您的注册验证码是：{code}，5分钟内有效。',
        CODE_TYPE::RESET_PASSWORD->value => '您的密码重置验证码是：{code}，5分钟内有效。',
    ],
];
