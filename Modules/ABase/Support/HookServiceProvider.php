<?php

declare(strict_types=1);

namespace Modules\ABase\Support;

use Modules\ABase\Hooks\Management\HookManager;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

/**
 * Hook 系统服务提供者基类
 *
 * 专门负责 Hook 系统的注册、配置和管理
 * 为其他模块提供 Hook 系统的基础服务
 *
 * 子类需要定义：
 * - $hooks: 可用的Hook定义类
 * - $hookHandlers: Hook处理器映射数组
 * - $hookSubscribers: Hook订阅者数组
 */
abstract class HookServiceProvider extends ServiceProvider
{
    /**
     * 可用的Hook定义类
     *
     * 子类应该重写此属性来定义自己的Hook类
     * 结构：[Hook类名, Hook类名, ...]
     *
     * @var array<string>
     */
    protected array $hooks = [];

    /**
     * Hook处理器映射数组
     *
     * 子类应该重写此属性来定义自己的Hook处理器
     * 结构：['Hook类名' => [处理器类名, 处理器类名, ...], ...]
     *
     * @var array<string, array<string>>
     */
    protected array $hookHandlers = [];

    /**
     * Hook订阅者数组
     *
     * 子类应该重写此属性来定义自己的Hook订阅者
     * 结构：[订阅者类名, 订阅者类名, ...]
     *
     * @var array<string>
     */
    protected array $hookSubscribers = [];

    /**
     * 注册服务
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/hooks.php',
            'abase.hooks'
        );
    }

    /**
     * 启动服务
     */
    public function boot(): void
    {
        // 在应用启动后初始化Hook系统
        $this->app->booted(function () {
            $this->initializeHookSystem();

            // 注册Hook定义
            $this->registerHookDefinitions();

            // 注册Hook处理器
            $this->registerHookHandlers();

            // 注册Hook订阅者
            $this->registerHookSubscribers();
        });
    }

    /**
     * 初始化Hook系统
     *
     * 子类可以重写此方法来自定义初始化逻辑
     */
    protected function initializeHookSystem(): void
    {
        // 默认实现为空，子类可以重写
    }

    /**
     * 注册Hook定义
     */
    protected function registerHookDefinitions(): void
    {
        foreach ($this->hooks as $hookClass) {
            try {
                if (class_exists($hookClass)) {
                    HookManager::registerHook($hookClass);
                }
            } catch (\Exception $e) {
                // 记录错误但不中断执行
                Log::warning("Failed to register hook definition {$hookClass}: " . $e->getMessage());
            }
        }
    }

    /**
     * 注册Hook处理器
     */
    protected function registerHookHandlers(): void
    {
        foreach ($this->hookHandlers as $hookClass => $handlers) {
            try {
                if (class_exists($hookClass)) {
                    HookManager::addHandlers($hookClass, $handlers);
                }
            } catch (\Exception $e) {
                // 记录错误但不中断执行
                Log::warning("Failed to register handlers for {$hookClass}: " . $e->getMessage());
            }
        }
    }

    /**
     * 注册Hook订阅者
     */
    protected function registerHookSubscribers(): void
    {
        foreach ($this->hookSubscribers as $subscriberClass) {
            try {
                if (class_exists($subscriberClass)) {
                    $subscriber = $this->app->make($subscriberClass);
                    HookManager::registerSubscriber($subscriber);
                }
            } catch (\Exception $e) {
                // 记录错误但不中断执行
                Log::warning("Failed to register subscriber {$subscriberClass}: " . $e->getMessage());
            }
        }
    }
}
