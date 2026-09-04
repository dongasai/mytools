<?php

declare(strict_types=1);

namespace Modules\ABase\Providers;

use Modules\ABase\Hooks\Definitions\DemoHook;
use Modules\ABase\Hooks\Handlers\DemoHookHandler;
use Modules\ABase\Hooks\Management\HookManager;
use Modules\ABase\Support\HookServiceProvider as BaseHookServiceProvider;

/**
 * ABase Hook 服务提供者
 *
 * 负责注册 ABase 模块的 Hook 处理器和订阅者
 * 提供模块间 Hook 系统的基础服务
 */
class HookServiceProvider extends BaseHookServiceProvider
{
    /**
     * 可用的Hook定义类
     *
     * @var array<string>
     */
    protected array $hooks = [
        DemoHook::class,
    ];

    /**
     * Hook处理器映射数组
     *
     * 结构：['Hook类名' => [处理器类名, 处理器类名, ...], ...]
     *
     * @var array<string, array<string>>
     */
    protected array $hookHandlers = [
        // Demo Hook 处理器
        'Modules\ABase\Hooks\Definitions\DemoHook' => [
            DemoHookHandler::class,
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
        // DemoHook 订阅者
        // \Modules\ABase\Hooks\Subscribers\DemoHookSubscriber::class,
    ];

    /**
     * 注册服务
     */
    public function register(): void
    {
        parent::register();

        // 注册Hook管理器为单例（静态类不需要实例化）
        $this->app->singleton(HookManager::class, function () {
            return new class
            {
                public function __call($method, $args)
                {
                    return forward_static_call([HookManager::class, $method], ...$args);
                }
            };
        });

        // 注册Hook管理器别名
        $this->app->alias(HookManager::class, 'abase.hooks.manager');
    }

    /**
     * 初始化Hook系统
     *
     * 重写基类方法以添加ABase特定的初始化逻辑
     */
    protected function initializeHookSystem(): void
    {
        // 启用调试模式（如果在开发环境）
        if ($this->app->environment('local', 'testing')) {
            HookManager::enableDebug();
        }

        // 配置DemoHook处理器
        $demoConfig = config('abase.hooks.' . DemoHookHandler::class, [
            'prefix' => 'demo',
            'multiplier' => 2,
            'enable_logging' => true,
        ]);

        DemoHookHandler::configure(
            defaultPrefix: $demoConfig['prefix'],
            multiplier: $demoConfig['multiplier'],
            enableLogging: $demoConfig['enable_logging']
        );
    }

    /**
     * 获取提供的服务
     *
     * @return array<string>
     */
    public function provides(): array
    {
        return array_merge(parent::provides(), [
            HookManager::class,
            'abase.hooks.manager',
            'abase.hooks',
        ]);
    }
}
