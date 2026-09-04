<?php

use Modules\ABase\Enums\ConfigDbBackupMode;
use Modules\ABase\Enums\ConfigDbCompressionType;
use Modules\ABase\Enums\ConfigDbNotificationChannel;
use Modules\ABase\Enums\ConfigDbType;

return [
    /*
    |--------------------------------------------------------------------------
    | 配置表数据库备份配置 - 使用枚举类型约束
    |--------------------------------------------------------------------------
    |
    | 此配置文件展示了如何使用枚举类型来增强配置的类型安全性
    | 所有枚举值都受到类型检查约束
    |
    */

    // 是否启用此模块的配置表备份
    'enabled' => true,

    // 模块显示名称
    'module_name' => 'EnumExample',

    // 模块描述
    'description' => '枚举类型约束示例 - 展示类型安全的配置',

    // 备份表配置 - 使用枚举类型
    'tables' => [
        /*
        |--------------------------------------------------------------------------
        | 模型类型示例 - 使用 ConfigDbType::MODEL 枚举
        |--------------------------------------------------------------------------
        */
        'example_model' => [
            'type' => ConfigDbType::MODEL->value,
            'model' => \Modules\Demo5\Models\Demo5Post::class,
            'description' => '使用枚举类型的文章模型配置示例',
            'condition' => [
                'status' => 'published',
            ],
            'order_by' => ['id', 'asc'],
            'exclude_columns' => ['created_at', 'updated_at'],
        ],

        /*
        |--------------------------------------------------------------------------
        | 前缀类型示例 - 使用 ConfigDbType::PREFIX 枚举
        |--------------------------------------------------------------------------
        */
        'example_prefix' => [
            'type' => ConfigDbType::PREFIX->value,
            'prefix' => 'example_',
            'description' => '使用枚举类型的前缀配置示例',
            'exclude_tables' => ['example_logs'],
            'condition' => null,
            'order_by' => ['id', 'asc'],
        ],

        /*
        |--------------------------------------------------------------------------
        | 特殊模式示例 - 使用 ConfigDbBackupMode 枚举
        |--------------------------------------------------------------------------
        */
        'example_structure_only' => [
            'type' => ConfigDbType::MODEL->value,
            'model' => \Modules\Demo5\Models\Demo5User::class,
            'description' => '只备份用户表结构 - 使用备份模式枚举',
            'backup_mode' => ConfigDbBackupMode::STRUCTURE_ONLY->value,
            'condition' => ['id' => -1], // 不满足条件，只备份结构
            'order_by' => ['id', 'asc'],
        ],

        /*
        |--------------------------------------------------------------------------
        | 只备份数据示例 - 使用 ConfigDbBackupMode 枚举
        |--------------------------------------------------------------------------
        */
        'example_data_only' => [
            'type' => ConfigDbType::MODEL->value,
            'model' => \Modules\Demo5\Models\Demo5Comment::class,
            'description' => '只备份评论数据 - 使用备份模式枚举',
            'backup_mode' => ConfigDbBackupMode::DATA_ONLY->value,
            'condition' => ['status' => 'approved'],
            'order_by' => ['id', 'asc'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 全局设置 - 使用枚举类型约束
    |--------------------------------------------------------------------------
    */
    'global_settings' => [
        // 备份模式 - 使用枚举确保类型安全
        'backup_mode' => ConfigDbBackupMode::FULL->value,

        // 压缩类型 - 使用枚举确保压缩类型正确
        'compression' => [
            'type' => ConfigDbCompressionType::NONE->value,
            'level' => ConfigDbCompressionType::NONE->getDefaultCompressionLevel(),
        ],

        // 通知渠道 - 使用枚举数组确保渠道有效
        'notification_channels' => [
            ConfigDbNotificationChannel::LOG->value,
            ConfigDbNotificationChannel::EMAIL->value,
        ],

        // 是否在文件头部添加模块信息
        'add_module_header' => true,

        // 是否添加统计信息
        'add_statistics' => true,

        // 是否压缩输出
        'compress_output' => false,

        // 默认排除的字段
        'default_exclude_columns' => [
            'laravel_through_key',
            'pivot_id',
        ],

        // 默认排序
        'default_order_by' => ['id', 'asc'],
    ],

    /*
    |--------------------------------------------------------------------------
    | 高级配置 - 使用枚举类型约束
    |--------------------------------------------------------------------------
    */
    'advanced_settings' => [
        // 备份加密设置
        'encryption' => [
            'enable' => false,
            'algorithm' => 'AES-256-CBC',
            'key_source' => 'env',
        ],

        // 压缩设置 - 使用枚举确保压缩类型正确
        'compression' => [
            'enable' => false,
            'type' => ConfigDbCompressionType::GZIP->value,
            'level' => ConfigDbCompressionType::GZIP->getDefaultCompressionLevel(),
        ],

        // 通知设置 - 使用枚举确保通知渠道有效
        'notifications' => [
            'on_success' => false,
            'on_failure' => true,
            'on_warning' => true,
            'channels' => [
                ConfigDbNotificationChannel::LOG->value,
                ConfigDbNotificationChannel::EMAIL->value,
                // ConfigDbNotificationChannel::SLACK->value,  // 可选启用
            ],
            'recipients' => ['admin@example.com'],
        ],

        // 备份模式 - 可以覆盖全局设置
        'default_backup_mode' => ConfigDbBackupMode::FULL->value,

        // 字符集设置
        'charset' => 'utf8mb4',

        // 调试设置
        'debug' => [
            'enable_query_log' => false,
            'log_slow_queries' => true,
            'slow_query_threshold' => 5.0,
            'export_debug_info' => false,
        ],

        // 性能优化
        'performance' => [
            'enable_parallel' => false,
            'max_workers' => 4,
            'chunk_size' => 1000,
            'memory_limit_per_worker' => '256M',
        ],

        // 保留策略
        'retention_policy' => [
            'max_files' => 10,
            'max_days' => 30,
            'max_size' => '1GB',
        ],

        // SQL选项
        'sql_options' => [
            'add_comments' => true,
            'include_foreign_keys' => true,
            'include_indexes' => true,
            'disable_foreign_key_checks' => false,
            'set_names' => true,
        ],

        // 错误处理
        'error_handling' => [
            'continue_on_error' => true,
            'max_errors' => 100,
            'log_errors' => true,
            'notify_on_error' => true,
        ],

        // 验证设置
        'validation' => [
            'enable' => true,
            'strict_mode' => false,
            'validate_connections' => true,
            'validate_tables' => true,
            'validate_columns' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 枚举验证配置
    |--------------------------------------------------------------------------
    |
    | 这些配置会在运行时验证枚举值的有效性
    |
    */
    'enum_validation' => [
        // 允许的配置类型
        'allowed_types' => [
            ConfigDbType::MODEL->value,
            ConfigDbType::PREFIX->value,
        ],

        // 允许的备份模式
        'allowed_backup_modes' => [
            ConfigDbBackupMode::FULL->value,
            ConfigDbBackupMode::STRUCTURE_ONLY->value,
            ConfigDbBackupMode::DATA_ONLY->value,
        ],

        // 允许的压缩类型
        'allowed_compression_types' => [
            ConfigDbCompressionType::NONE->value,
            ConfigDbCompressionType::GZIP->value,
            ConfigDbCompressionType::ZIP->value,
        ],

        // 允许的通知渠道
        'allowed_notification_channels' => [
            ConfigDbNotificationChannel::LOG->value,
            ConfigDbNotificationChannel::EMAIL->value,
            ConfigDbNotificationChannel::WEBHOOK->value,
        ],

        // 是否启用严格模式验证
        'strict_validation' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | 枚举配置示例 - 展示如何获取枚举信息
    |--------------------------------------------------------------------------
    |
    | 这些配置展示了如何从枚举中获取有用的信息
    |
    */
    'enum_examples' => [
        // 获取所有配置类型
        'config_types' => array_map(fn($type) => [
            'value' => $type->value,
            'description' => $type->getDescription(),
            'icon' => $type->getIcon(),
        ], ConfigDbType::cases()),

        // 获取所有备份模式
        'backup_modes' => array_map(fn($mode) => [
            'value' => $mode->value,
            'description' => $mode->getDescription(),
            'icon' => $mode->getIcon(),
            'includes_structure' => $mode->includesStructure(),
            'includes_data' => $mode->includesData(),
            'file_suffix' => $mode->getFileSuffix(),
        ], ConfigDbBackupMode::cases()),

        // 获取所有压缩类型
        'compression_types' => array_map(fn($type) => [
            'value' => $type->value,
            'description' => $type->getDescription(),
            'icon' => $type->getIcon(),
            'file_extension' => $type->getFileExtension(),
            'supports_compression' => $type->supportsCompression(),
            'default_level' => $type->getDefaultCompressionLevel(),
        ], ConfigDbCompressionType::cases()),

        // 获取所有通知渠道
        'notification_channels' => array_map(fn($channel) => [
            'value' => $channel->value,
            'description' => $channel->getDescription(),
            'icon' => $channel->getIcon(),
            'is_async' => $channel->isAsync(),
            'required_config' => $channel->getRequiredConfig(),
        ], ConfigDbNotificationChannel::cases()),
    ],

    /*
    |--------------------------------------------------------------------------
    | 自定义处理器配置
    |--------------------------------------------------------------------------
    */
    'custom_processors' => [
        'before_backup' => [
            // \Module\Example\Processors\BackupPreProcessor::class
        ],
        'after_backup' => [
            // \Module\Example\Processors\BackupPostProcessor::class
        ],
        'table_processors' => [
            'example_table' => [
                // \Module\Example\Processors\ExampleTableProcessor::class
            ],
        ],
        'error_processors' => [
            // \Module\Example\Processors\ErrorProcessor::class
        ],
    ],
];
