<?php

/**
 * Notification模块后台菜单配置.
 *
 * 模块后台菜单ID: 33*** Notification模块专用
 */
return [
    // 顶级父菜单（通知管理分组）
    [
        'id' => 33001,
        'title' => '通知管理',
        'icon' => 'feather icon-bell',
        'uri' => '',
        'parent_id' => 0,
    ],
    // 子菜单：通知日志
    [
        'id' => 33002,
        'title' => '通知日志',
        'icon' => 'feather icon-file-text',
        'uri' => 'module_notification/notification-logs',
        'parent_id' => 33001,
    ],
    // 子菜单：通知模板
    [
        'id' => 33003,
        'title' => '通知模板',
        'icon' => 'feather icon-file-plus',
        'uri' => 'module_notification/notification-templates',
        'parent_id' => 33001,
    ],
];