<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AClean Module Configuration
    |--------------------------------------------------------------------------
    |
    | 这里是 AClean 模块的配置文件，包含了模块的各种设置选项
    |
    */

    /*
    |--------------------------------------------------------------------------
    | 基础配置
    |--------------------------------------------------------------------------
    */
    'enabled' => env('CLEANUP_ENABLED', true),
    'debug' => env('CLEANUP_DEBUG', false),
    'timezone' => env('CLEANUP_TIMEZONE', 'Asia/Shanghai'),

    /*
    |--------------------------------------------------------------------------
    | 数据库配置
    |--------------------------------------------------------------------------
    */
    'database' => [
        // 表前缀
        'table_prefix' => env('CLEANUP_TABLE_PREFIX', ''),

        // 扫描的数据库连接
        'connection' => env('CLEANUP_DB_CONNECTION', 'mysql'),

        // 批处理大小
        'batch_size' => [
            'default' => env('CLEANUP_BATCH_SIZE', 1000),
            'min' => 100,
            'max' => 10000,
        ],

        // 查询超时时间（秒）
        'query_timeout' => env('CLEANUP_QUERY_TIMEOUT', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | 执行配置
    |--------------------------------------------------------------------------
    */
    'execution' => [
        // 最大并发任务数
        'max_concurrent_tasks' => env('CLEANUP_MAX_CONCURRENT', 3),

        // 任务执行超时时间（秒）
        'task_timeout' => env('CLEANUP_TASK_TIMEOUT', 3600),

        // 是否启用预览模式
        'enable_preview' => env('CLEANUP_ENABLE_PREVIEW', true),

        // 是否启用确认步骤
        'require_confirmation' => env('CLEANUP_REQUIRE_CONFIRMATION', true),


        // 内存限制（MB）
        'memory_limit_mb' => env('CLEANUP_MEMORY_LIMIT', 512),

        // 进度更新间隔（秒）
        'progress_update_interval' => env('CLEANUP_PROGRESS_INTERVAL', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | 日志配置
    |--------------------------------------------------------------------------
    */
    'logging' => [
        // 是否启用详细日志
        'detailed_logging' => env('CLEANUP_DETAILED_LOG', true),

        // 日志保留天数
        'retention_days' => env('CLEANUP_LOG_RETENTION', 30),

        // 日志级别
        'level' => env('CLEANUP_LOG_LEVEL', 'info'),

        // 日志文件路径
        'file_path' => env('CLEANUP_LOG_PATH', storage_path('logs/aclean.log')),

        // 是否记录SQL语句
        'log_sql' => env('CLEANUP_LOG_SQL', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | 安全配置
    |--------------------------------------------------------------------------
    */
    'security' => [
        // 受保护的表（永远不会被清理）
        'protected_tables' => [
            'users',
            'user_profiles',
            'configs',
            'system_configs',
            'permissions',
            'roles',
            'migrations',
        ],

        // 受保护的模块
        'protected_modules' => [
            'User',
            'Permission',
            'Config',
            'System',
        ],

        // 受保护的数据分类
        'protected_categories' => [
            5, // 配置数据
        ],

        // 是否启用IP白名单
        'enable_ip_whitelist' => env('CLEANUP_IP_WHITELIST', false),

        // IP白名单
        'ip_whitelist' => explode(',', env('CLEANUP_ALLOWED_IPS', '127.0.0.1')),

        // 是否需要管理员权限
        'require_admin' => env('CLEANUP_REQUIRE_ADMIN', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | 性能配置
    |--------------------------------------------------------------------------
    */
    'performance' => [
        // 是否启用查询缓存
        'enable_query_cache' => env('CLEANUP_QUERY_CACHE', true),

        // 缓存TTL（秒）
        'cache_ttl' => env('CLEANUP_CACHE_TTL', 3600),

        // 是否启用表统计缓存
        'enable_stats_cache' => env('CLEANUP_STATS_CACHE', true),

        // 统计缓存TTL（秒）
        'stats_cache_ttl' => env('CLEANUP_STATS_CACHE_TTL', 1800),

        // 是否启用分页
        'enable_pagination' => env('CLEANUP_PAGINATION', true),

        // 分页大小
        'page_size' => env('CLEANUP_PAGE_SIZE', 50),
    ],

    /*
    |--------------------------------------------------------------------------
    | 通知配置
    |--------------------------------------------------------------------------
    */
    'notifications' => [
        // 是否启用通知
        'enabled' => env('CLEANUP_NOTIFICATIONS', true),

        // 通知渠道
        'channels' => [
            'mail' => env('CLEANUP_NOTIFY_MAIL', true),
            'database' => env('CLEANUP_NOTIFY_DB', true),
            'slack' => env('CLEANUP_NOTIFY_SLACK', false),
        ],

        // 邮件通知配置
        'mail' => [
            'to' => explode(',', env('CLEANUP_MAIL_TO', 'admin@example.com')),
            'subject_prefix' => env('ACLEAN_MAIL_PREFIX', '[AClean]'),
        ],

        // Slack通知配置
        'slack' => [
            'webhook_url' => env('CLEANUP_SLACK_WEBHOOK'),
            'channel' => env('CLEANUP_SLACK_CHANNEL', '#aclean'),
            'username' => env('CLEANUP_SLACK_USERNAME', 'AClean Bot'),
        ],

        // 通知事件
        'events' => [
            'task_completed' => true,
            'task_failed' => true,
            'large_cleanup' => true, // 大量数据清理时通知
        ],

        // 大量数据阈值
        'large_cleanup_threshold' => env('CLEANUP_LARGE_THRESHOLD', 100000),
    ],

    /*
    |--------------------------------------------------------------------------
    | 默认清理配置
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        // 数据分类的默认配置
        'categories' => [
            1 => [ // 用户数据
                'cleanup_type' => 3, // 按时间删除
                'priority' => 100,
                'batch_size' => 1000,
                'is_enabled' => false,
                'backup_enabled' => true,
                'conditions' => [
                    'time_field' => 'created_at',
                    'time_value' => 90,
                    'time_unit' => 'days',
                ],
            ],
            2 => [ // 日志数据
                'cleanup_type' => 3, // 按时间删除
                'priority' => 200,
                'batch_size' => 5000,
                'is_enabled' => true,
                'backup_enabled' => false,
                'conditions' => [
                    'time_field' => 'created_at',
                    'time_value' => 30,
                    'time_unit' => 'days',
                ],
            ],
            3 => [ // 交易数据
                'cleanup_type' => 3, // 按时间删除
                'priority' => 150,
                'batch_size' => 1000,
                'is_enabled' => false,
                'backup_enabled' => true,
                'conditions' => [
                    'time_field' => 'created_at',
                    'time_value' => 365,
                    'time_unit' => 'days',
                ],
            ],
            4 => [ // 缓存数据
                'cleanup_type' => 1, // 清空表
                'priority' => 400,
                'batch_size' => 10000,
                'is_enabled' => true,
                'backup_enabled' => false,
                'conditions' => null,
            ],
            5 => [ // 配置数据
                'cleanup_type' => 5, // 按条件删除
                'priority' => 999,
                'batch_size' => 100,
                'is_enabled' => false,
                'backup_enabled' => true,
                'conditions' => null,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 监控配置
    |--------------------------------------------------------------------------
    */
    'monitoring' => [
        // 是否启用监控
        'enabled' => env('CLEANUP_MONITORING', true),

        // 监控间隔（秒）
        'interval' => env('CLEANUP_MONITOR_INTERVAL', 60),

        // 健康检查
        'health_check' => [
            'max_running_tasks' => 5,
            'max_failed_tasks_24h' => 10,
        ],

        // 性能指标
        'metrics' => [
            'track_execution_time' => true,
            'track_memory_usage' => true,
            'track_disk_usage' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | API配置
    |--------------------------------------------------------------------------
    */
    'api' => [
        // API版本
        'version' => 'v1',

        // API前缀
        'prefix' => 'api/aclean',

        // 是否启用API认证
        'auth_required' => env('CLEANUP_API_AUTH', true),

        // API限流
        'rate_limit' => [
            'enabled' => env('CLEANUP_API_RATE_LIMIT', true),
            'max_attempts' => env('CLEANUP_API_MAX_ATTEMPTS', 60),
            'decay_minutes' => env('CLEANUP_API_DECAY_MINUTES', 1),
        ],
    ],
];
