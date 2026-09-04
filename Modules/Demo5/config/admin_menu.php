<?php

// Demo5模块后台菜单配置
// 基于Laravel最佳实践，遵循Dcat Admin规范
// 模块后台菜单ID: 12*** 每个模块都不一样,不能重复,DEMO5的是12---
return [
    [
        'id' => 12001,
        'title' => 'DEMO5',
        'icon' => 'feather icon-layers',
        'uri' => '',
        'parent_id' => 0,
    ],
    [
        'id' => 12002,
        'title' => '仪表盘',
        'icon' => 'feather icon-home',
        'uri' => 'module_demo5/dashboard',
        'parent_id' => 12001,
    ],
    [
        'id' => 12005,
        'title' => 'Vue仪表盘',
        'icon' => 'feather icon-monitor',
        'uri' => 'module_demo5/vue-dashboard',
        'parent_id' => 12001,
    ],
    [
        'id' => 12006,
        'title' => 'Vue文章管理',
        'icon' => 'feather icon-file-text',
        'uri' => 'module_demo5/vue-posts',
        'parent_id' => 12001,
        'order' => 12006,
    ],
    [
        'id' => 12003,
        'title' => '文章管理',
        'icon' => 'feather icon-file-text',
        'uri' => 'module_demo5/posts',
        'parent_id' => 12001,
    ],
    [
        'id' => 12004,
        'title' => '评论管理',
        'icon' => 'feather icon-message-circle',
        'uri' => 'module_demo5/comments',
        'parent_id' => 12001,
    ],
];