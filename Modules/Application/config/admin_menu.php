<?php

/**
 * Application模块后台菜单配置
 * 模块后台菜单ID: 15*** Application模块专用
 */
return [
    // 顶级父菜单
    [
        'id' => 15001,
        'title' => '应用管理',
        'icon' => 'feather icon-settings',
        'uri' => '',
        'parent_id' => 0,
    ],
    // 系统配置相关
    [
        'id' => 15002,
        'title' => '系统配置',
        'icon' => 'feather icon-sliders',
        'uri' => 'module_application/config',
        'parent_id' => 15001,
    ],
    [
        'id' => 15004,
        'title' => '系统全局状态',
        'icon' => 'feather icon-activity',
        'uri' => 'module_application/home',
        'parent_id' => 15001,
    ],
    [
        'id' => 15003,
        'title' => '配置管理',
        'icon' => 'feather icon-edit-3',
        'uri' => 'module_application/config-admin',
        'parent_id' => 15001,
    ],
    // 日志管理分组
    [
        'id' => 15011,
        'title' => '日志管理',
        'icon' => 'feather icon-file-text',
        'uri' => '',
        'parent_id' => 15001,
    ],
    [
        'id' => 15012,
        'title' => '系统日志',
        'icon' => 'feather icon-activity',
        'uri' => 'module_application/system-log',
        'parent_id' => 15011,
    ],
    [
        'id' => 15013,
        'title' => '操作日志',
        'icon' => 'feather icon-edit',
        'uri' => 'module_application/action-log',
        'parent_id' => 15011,
    ],
    [
        'id' => 15014,
        'title' => '请求日志',
        'icon' => 'feather icon-server',
        'uri' => 'module_application/request-logs',
        'parent_id' => 15011,
    ],
    // 任务管理分组
    [
        'id' => 15021,
        'title' => '任务管理',
        'icon' => 'feather icon-cpu',
        'uri' => '',
        'parent_id' => 15001,
    ],
    [
        'id' => 15022,
        'title' => '队列任务',
        'icon' => 'feather icon-list',
        'uri' => 'module_application/jobs',
        'parent_id' => 15021,
    ],
    [
        'id' => 15023,
        'title' => '失败任务',
        'icon' => 'feather icon-x-circle',
        'uri' => 'module_application/failed-jobs',
        'parent_id' => 15021,
    ],
    [
        'id' => 15024,
        'title' => '任务批次',
        'icon' => 'feather icon-package',
        'uri' => 'module_application/job-batches',
        'parent_id' => 15021,
    ],
    [
        'id' => 15025,
        'title' => '任务运行',
        'icon' => 'feather icon-play',
        'uri' => 'module_application/job-runs',
        'parent_id' => 15021,
    ],
    // 系统工具分组
    [
        'id' => 15031,
        'title' => '系统工具',
        'icon' => 'feather icon-tool',
        'uri' => '',
        'parent_id' => 15001,
    ],
    [
        'id' => 15032,
        'title' => '管理视图',
        'icon' => 'feather icon-eye',
        'uri' => 'module_application/admin-views',
        'parent_id' => 15031,
    ],
    // 功能开关分组
    [
        'id' => 15041,
        'title' => '功能开关',
        'icon' => 'feather icon-toggle-left',
        'uri' => '',
        'parent_id' => 15001,
    ],
    [
        'id' => 15042,
        'title' => '功能管理',
        'icon' => '',
        'uri' => 'module_application/features',
        'parent_id' => 15041,
    ],
    // 字典管理分组
    [
        'id' => 15051,
        'title' => '字典管理',
        'icon' => 'feather icon-book',
        'uri' => '',
        'parent_id' => 15001,
    ],
    [
        'id' => 15052,
        'title' => '字典数据',
        'icon' => '',
        'uri' => 'module_application/dict',
        'parent_id' => 15051,
    ],
];