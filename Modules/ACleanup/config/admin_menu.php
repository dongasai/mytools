<?php

// AClean模块后台菜单配置
// 菜单ID范围: 35000 系列
return [
    [
        'id' => 35001,
        'parent_id' => 0,
        'order' => 35001,
        'title' => '数据清理管理',
        'icon' => 'feather icon-trash-2',
        'uri' => '',
    ],
    [
        'id' => 35002,
        'parent_id' => 35001,
        'order' => 35002,
        'title' => '清理配置',
        'icon' => '',
        'uri' => 'module_aclean/configs',
    ],
    [
        'id' => 35003,
        'parent_id' => 35001,
        'order' => 35003,
        'title' => '清理计划',
        'icon' => '',
        'uri' => 'module_aclean/plans',
    ],
    [
        'id' => 35004,
        'parent_id' => 35001,
        'order' => 35004,
        'title' => '清理任务',
        'icon' => '',
        'uri' => 'module_aclean/tasks',
    ],
    [
        'id' => 35005,
        'parent_id' => 35001,
        'order' => 35005,
        'title' => '清理日志',
        'icon' => '',
        'uri' => 'module_aclean/logs',
    ],
    [
        'id' => 35006,
        'parent_id' => 35001,
        'order' => 35006,
        'title' => '统计信息',
        'icon' => '',
        'uri' => 'module_aclean/stats',
    ],
];
