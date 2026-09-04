<?php

// FeatureSms 模块后台菜单配置
// 基于Laravel最佳实践，遵循Dcat Admin规范
// 模块后台菜单ID: 500*** 一共六位，前缀三位，可用三位 FeatureSms 短信模块
// 每个模块都不一样,不能重复
return [
    [
        'id' => 500001,
        'title' => '短信管理',
        'icon' => 'feather icon-message-square',
        'uri' => '',
        'parent_id' => 0,
    ],
    [
        'id' => 500002,
        'title' => '仪表盘',
        'icon' => 'feather icon-activity',
        'uri' => 'featuresms/dashboard',
        'parent_id' => 500001,
    ],
    [
        'id' => 500003,
        'title' => '短信配置',
        'icon' => 'feather icon-settings',
        'uri' => 'featuresms/sms-configs',
        'parent_id' => 500001,
    ],
    [
        'id' => 500004,
        'title' => 'db驱动-短信记录',
        'icon' => 'feather icon-database',
        'uri' => 'featuresms/sms-records',
        'parent_id' => 500001,
    ],
    [
        'id' => 500005,
        'title' => '短信记录',
        'icon' => 'feather icon-file-text',
        'uri' => 'featuresms/sms-logs',
        'parent_id' => 500001,
    ],
    [
        'id' => 500006,
        'title' => '验证码管理',
        'icon' => 'feather icon-shield',
        'uri' => 'featuresms/sms-codes',
        'parent_id' => 500001,
    ],
];
