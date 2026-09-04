<?php

declare(strict_types=1);

namespace Modules\ABase\Support\Traits;

use Modules\ABase\Hooks\Management\HookManager;
use Illuminate\Support\Facades\Log;

/**
 * Hook 服务提供者 Trait
 *
 * 为模块服务提供者提供 Hook 系统注册功能
 * 使用方式与 RouteServiceProviderTrait 类似
 *
 * 使用示例：
 * class MyServiceProvider extends ServiceProvider
 * {
 *     use HookServiceProviderTrait;
 *
 *     // 必须定义此属性
 *     protected array $hookHandlers = [
 *         SomeHook::class => [SomeHandler::class],
 *     ];
 *
 *     public function boot(): void
 *     {
 *         $this->modulePath = dirname(__DIR__);
 *         parent::boot();
 *         $this->registerHookHandlers(); // 自动注册 Hook 处理器
 *     }
 * }
 *
 * 需要定义的属性：
 * - $hookHandlers: array Hook处理器映射
 * - $hookSubscribers: array Hook订阅者数组（可选）
 */
trait HookServiceProviderTrait
{
    /**
     * 注册Hook处理器
     *
     * 在服务提供者的 boot() 方法中调用
     * 自动处理 Hook 注册时序问题
     */
    protected function registerHookHandlers(): void
    {
        // 检查是否定义了 hookHandlers 属性
        if (!property_exists($this, 'hookHandlers') || empty($this->hookHandlers)) {
            return;
        }

        $this->app->booted(function () {
            foreach ($this->hookHandlers as $hookClass => $handlers) {
                try {
                    if (class_exists($hookClass)) {
                        // 确保 Hook 已注册
                        if (!HookManager::isHookRegistered($hookClass)) {
                            HookManager::registerHook($hookClass);
                        }

                        // 添加处理器
                        HookManager::addHandlers($hookClass, $handlers);
                    }
                } catch (\Exception $e) {
                    Log::warning("Failed to register handlers for {$hookClass}: " . $e->getMessage());
                }
            }
        });
    }

    /**
     * 注册Hook订阅者
     *
     * 在服务提供者的 boot() 方法中调用
     */
    protected function registerHookSubscribers(): void
    {
        // 检查是否定义了 hookSubscribers 属性
        if (!property_exists($this, 'hookSubscribers') || empty($this->hookSubscribers)) {
            return;
        }

        $this->app->booted(function () {
            foreach ($this->hookSubscribers as $subscriberClass) {
                try {
                    if (class_exists($subscriberClass)) {
                        $subscriber = $this->app->make($subscriberClass);
                        HookManager::registerSubscriber($subscriber);
                    }
                } catch (\Exception $e) {
                    Log::warning("Failed to register subscriber {$subscriberClass}: " . $e->getMessage());
                }
            }
        });
    }
}