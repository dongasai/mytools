<?php

// AFile 模块后台菜单配置
// 菜单ID范围: 31*** 系列
return [
    // 顶级父菜单
    [
        'id'          => 31001,
        'parent_id'   => 0,
        'order'       => 31001,
        'title'       => '文件管理',
        'icon'        => 'feather icon-folder',
        'uri'         => '',
    ],
    // 子菜单 - 文件列表
    [
        'id'          => 31002,
        'parent_id'   => 31001,
        'order'       => 31002,
        'title'       => '文件列表',
        'icon'        => '',
        'uri'         => 'module_afile/files',
    ],
    // 子菜单 - 图片管理
    [
        'id'          => 31003,
        'parent_id'   => 31001,
        'order'       => 31003,
        'title'       => '图片管理',
        'icon'        => '',
        'uri'         => 'module_afile/images',
    ],
    // 子菜单 - 存储配置
    [
        'id'          => 31004,
        'parent_id'   => 31001,
        'order'       => 31004,
        'title'       => '存储配置',
        'icon'        => '',
        'uri'         => 'module_afile/storage-configs',
    ],
    // 子菜单 - 文件模板
    [
        'id'          => 31005,
        'parent_id'   => 31001,
        'order'       => 31005,
        'title'       => '文件模板',
        'icon'        => '',
        'uri'         => 'module_afile/file-templates',
    ],
];