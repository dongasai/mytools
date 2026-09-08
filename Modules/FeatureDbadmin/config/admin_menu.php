<?php

/**
 * FeatureDbadmin 模块后台菜单配置
 * 菜单ID范围: 103*** 系列（Feature* 功能模块）
 */
return [
    // 单一菜单项 - 数据库管理员
    [
        'id'          => 103001,
        'parent_id'   => 0,
        'order'       => 103001,
        'title'       => '数据库管理员',
        'icon'        => 'feather icon-database',
        'uri'         => 'featuredbadmin/dashboard',
    ],
];