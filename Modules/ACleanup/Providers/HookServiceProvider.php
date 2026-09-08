<?php

declare(strict_types=1);

namespace Modules\AClean\Providers;

/**
 * Cleanup Hook 服务提供者
 *
 * 负责注册 Cleanup 模块的 Hook 处理器和订阅者
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
        // 可以在这里添加Hook定义类
    ];

    /**
     * Hook处理器映射数组
     *
     * 结构：['Hook类名' => [处理器类名, 处理器类名, ...], ...]
     *
     * @var array<string, array<string>>
     */
    protected array $hookHandlers = [
        // 可以在这里添加Hook处理器
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
    ];
}
