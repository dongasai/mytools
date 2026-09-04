<?php

use Modules\ABase\Enums\ConfigDbType;

return [
    /*
    |--------------------------------------------------------------------------
    | ABase模块配置表备份配置
    |--------------------------------------------------------------------------
    |
    | 基础模块的核心配置表备份定义
    | 包含系统配置、用户权限、基础数据等核心表
    |
    */

    // 是否启用此模块的配置表备份
    'enabled' => true,

    // 模块显示名称
    'module_name' => 'ABase',

    // 模块描述
    'description' => '基础核心模块配置表备份',

    // 备份表配置
    'tables' => [
        /*
        |--------------------------------------------------------------------------
        | 系统配置类
        |--------------------------------------------------------------------------
        */
        'system_configs' => [
            'type' => ConfigDbType::MODEL->value,
            'model' => \Modules\Application\Models\ApplicationConfig::class,
            'description' => '应用核心配置表 - 包含应用运行的关键配置参数',
            'condition' => [
                'is_client' => 'yes',  // 只备份客户端配置
            ],
            'order_by' => ['group', 'asc', 'keyname', 'asc'],
            'exclude_columns' => [
                'created_at',
                'updated_at',
                'deleted_at',
                'options',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 权限相关配置
        |--------------------------------------------------------------------------
        */
        'admin_permissions' => [
            'type' => ConfigDbType::MODEL->value,
            'model' => \Dcat\Admin\Models\Permission::class,
            'description' => '管理后台权限配置 - 定义系统访问权限和操作授权',
            'condition' => null,
            'order_by' => ['order', 'asc', 'id', 'asc'],
            'exclude_columns' => [
                'created_at',
                'updated_at',
            ],
        ],

        'admin_roles' => [
            'type' => ConfigDbType::MODEL->value,
            'model' => \Dcat\Admin\Models\Role::class,
            'description' => '管理后台角色配置 - 定义用户角色和权限组（排除超级管理员）',
            'condition' => [
                'slug' => '!=' . 'super-admin',  // 排除超级管理员角色
            ],
            'order_by' => ['order', 'asc', 'id', 'asc'],
        ],

        /*
        |--------------------------------------------------------------------------
        | 基础数据配置 - 使用前缀批量处理
        |--------------------------------------------------------------------------
        */
        'base_config_tables' => [
            'type' => ConfigDbType::PREFIX->value,
            'prefix' => 'base_',
            'description' => '基础模块配置表 - 批量处理所有base_前缀的表（排除临时和缓存表）',
            'exclude_tables' => [
                'base_logs',            // 排除日志表
                'base_temp_data',       // 排除临时数据
                'base_cache',           // 排除缓存表
                'base_sessions',         // 排除会话表
            ],
            'condition' => [
                'status' => 'active',
            ],
            'order_by' => ['id', 'asc'],
            'exclude_columns' => [
                'created_at',
                'updated_at',
                'deleted_at',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 字典数据配置
        |--------------------------------------------------------------------------
        */
        'dictionaries' => [
            'type' => ConfigDbType::PREFIX->value,
            'prefix' => 'dict_',
            'description' => '系统字典数据表 - 包含系统所有枚举值和字典数据',
            'exclude_tables' => [],
            'condition' => [
                'is_enabled' => true,
            ],
            'order_by' => ['type', 'asc', 'sort', 'asc'],
            'exclude_columns' => [
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
            ],
        ],

    ],

];
