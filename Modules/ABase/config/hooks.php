<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Hook 系统配置
    |--------------------------------------------------------------------------
    |
    | Hook系统的基础配置
    |
    */

    /*
    |--------------------------------------------------------------------------
    | DemoHookHandler 配置
    |--------------------------------------------------------------------------
    |
    | DemoHookHandler 的默认配置参数
    |
    */
    \Modules\ABase\Hooks\Handlers\DemoHookHandler::class => [
        'prefix' => env('DEMO_HOOK_PREFIX', 'demo'),
        'multiplier' => env('DEMO_HOOK_MULTIPLIER', 2),
        'enable_logging' => env('DEMO_HOOK_ENABLE_LOGGING', true),
    ],
];
