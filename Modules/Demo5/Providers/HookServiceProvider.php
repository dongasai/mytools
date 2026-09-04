<?php

declare(strict_types=1);

namespace Modules\Demo5\Providers;

use Modules\Demo5\Hooks\Handlers\PostContentFilterHandler;
use Modules\Demo5\Hooks\Handlers\ArticleFilterHookHandler;

/**
 * Demo5 Hook 服务提供者
 *
 * 负责注册 Demo5 模块的 Hook 处理器和订阅者
 */
class HookServiceProvider extends \Modules\ABase\Support\HookServiceProvider
{
    /**
     * 可用的Hook定义类
     *
     * 结构：[Hook类名, Hook类名, ...]
     *
     * @var array<string>
     */
    protected array $hooks = [
        // 文章内容过滤Hook定义
        \Modules\Demo5\Hooks\Definitions\PostContentFilterHook::class,
        // 文章内容过滤Hook（ArticleFilterHook）
        \Modules\Demo5\Hooks\Definitions\ArticleFilterHook::class,
    ];

    /**
     * Hook处理器映射数组
     *
     * 结构：['Hook类名' => [处理器类名, 处理器类名, ...], ...]
     *
     * @var array<string, array<string>>
     */
    protected array $hookHandlers = [
        // 文章内容过滤Hook处理器
        \Modules\Demo5\Hooks\Definitions\PostContentFilterHook::class => [
            PostContentFilterHandler::class,
        ],
        // ArticleFilterHook处理器
        \Modules\Demo5\Hooks\Definitions\ArticleFilterHook::class => [
            ArticleFilterHookHandler::class,
        ],
    ];

    /**
     * Hook订阅者数组
     *
     * 结构：[订阅者类名, 订阅者类名, ...]
     *
     * @var array<string>
     */
    protected array $hookSubscribers = [
        // 可以在这里添加订阅者类名
        // Example: \Modules\Demo5\Hooks\Subscribers\PostSubscriber::class,
    ];
}
