<?php

declare(strict_types=1);

namespace Modules\ABase\Hooks\Management;

use Modules\ABase\Hooks\Core\HookParameter;
use Modules\ABase\Hooks\Core\HookResult;
use Modules\ABase\Hooks\Core\HookSubscriberInterface;
use Modules\ABase\Hooks\Management\HookHandlerProxy;
use InvalidArgumentException;

/**
 * Hook助手类
 *
 * 提供友好的API封装，简化Hook系统的使用
 */
class Hooks
{
    /**
     * 注册Hook定义
     *
     * @param  string  $hookClass  Hook类名
     */
    public static function register(string $hookClass): void
    {
        HookManager::registerHook($hookClass);
    }

    /**
     * 添加Hook处理器
     *
     * @param  string  $hookClass  Hook类名
     * @param  string  $handlerClass  处理器类名
     * @param  int  $sort  排序值，范围1-100，数值越小越早执行，默认10
     */
    public static function add(string $hookClass, string $handlerClass, int $sort = 10): void
    {
        HookManager::add($hookClass, $handlerClass, $sort);
    }

    /**
     * 添加高优先级处理器
     *
     * 排序值设为1，确保处理器最早执行
     *
     * @param  string  $hookClass  Hook类名
     * @param  string  $handlerClass  处理器类名
     */
    public static function addFirst(string $hookClass, string $handlerClass): void
    {
        HookManager::add($hookClass, $handlerClass, 1);
    }

    /**
     * 添加低优先级处理器
     *
     * 排序值设为50，确保处理器最晚执行
     *
     * @param  string  $hookClass  Hook类名
     * @param  string  $handlerClass  处理器类名
     */
    public static function addLast(string $hookClass, string $handlerClass): void
    {
        HookManager::add($hookClass, $handlerClass, 50);
    }

    /**
     * 批量添加处理器
     *
     * @param  string  $hookClass  Hook类名
     * @param  array  $handlers  处理器数组，可以是字符串或包含class、priority、acceptedArgs的数组
     *
     * @throws InvalidArgumentException 当处理器数据格式不正确时抛出异常
     */
    public static function addMany(string $hookClass, array $handlers): void
    {
        HookManager::addHandlers($hookClass, $handlers);
    }

    /**
     * 移除处理器
     *
     * @param  string  $hookClass  Hook类名
     * @param  string  $handlerClass  处理器类名
     */
    public static function remove(string $hookClass, string $handlerClass): void
    {
        HookManager::removeHandler($hookClass, $handlerClass);
    }

    /**
     * 检查是否有处理器
     *
     * @param  string  $hookClass  Hook类名
     * @return bool 如果有处理器返回true，否则返回false
     */
    public static function hasHandlers(string $hookClass): bool
    {
        return HookManager::hasHandlers($hookClass);
    }

    /**
     * 获取处理器数量
     *
     * @param  string  $hookClass  Hook类名
     * @return int 处理器数量
     */
    public static function countHandlers(string $hookClass): int
    {
        return count(HookManager::getHandlers($hookClass));
    }

    /**
     * 应用Hook（简单版本）
     *
     * 执行指定Hook的所有处理器，按照优先级顺序处理参数
     *
     * @param  string  $hookClass  Hook类名
     * @param  HookParameter  $parameter  Hook参数对象
     * @return HookResult Hook执行结果
     *
     * @throws \Exception 当Hook未注册或执行深度超限时抛出异常
     */
    public static function apply(string $hookClass, HookParameter $parameter): HookResult
    {
        return HookManager::apply($hookClass, $parameter);
    }

    /**
     * 应用Hook（带初始结果）
     *
     * 执行Hook处理器，从指定的初始结果开始处理
     *
     * @param  string  $hookClass  Hook类名
     * @param  HookParameter  $parameter  Hook参数对象
     * @param  HookResult  $initialResult  初始结果对象
     * @return HookResult Hook执行结果
     *
     * @throws \Exception 当Hook未注册或执行深度超限时抛出异常
     */
    public static function applyWithResult(string $hookClass, HookParameter $parameter, HookResult $initialResult): HookResult
    {
        return HookManager::apply($hookClass, $parameter, $initialResult);
    }

    /**
     * 获取Hook处理器
     *
     * 返回按优先级排序的处理器列表
     *
     * @param  string  $hookClass  Hook类名
     * @return array 处理器数组，按优先级排序
     */
    public static function getHandlers(string $hookClass): array
    {
        return HookManager::getHandlers($hookClass);
    }

    /**
     * 启用调试模式
     *
     * 启用后将记录Hook执行的详细日志信息
     */
    public static function enableDebug(): void
    {
        HookManager::enableDebug();
    }

    /**
     * 禁用调试模式
     *
     * 禁用后停止记录Hook执行日志
     */
    public static function disableDebug(): void
    {
        HookManager::disableDebug();
    }

    /**
     * 检查调试模式是否启用
     *
     * @return bool 如果调试模式启用返回true，否则返回false
     */
    public static function isDebugging(): bool
    {
        return HookManager::isDebugEnabled();
    }

    /**
     * 获取执行日志
     *
     * 返回Hook执行的详细日志记录，包括执行时间、参数、结果等信息
     *
     * @return array 执行日志数组
     */
    public static function getLog(): array
    {
        return HookManager::getExecutionLog();
    }

    /**
     * 清空执行日志
     *
     * 清除所有Hook执行记录
     */
    public static function clearLog(): void
    {
        HookManager::clearExecutionLog();
    }

    /**
     * 获取调试信息
     *
     * 返回Hook系统的完整调试信息，包括统计、处理器状态、执行日志等
     *
     * @return array 调试信息数组，包含：
     *               - hook_count: Hook总数
     *               - registered_hooks: 已注册的Hook列表
     *               - hook_stats: Hook统计信息
     *               - total_handler_instances: 处理器实例总数
     *               - debug_mode: 调试模式状态
     *               - execution_log: 执行日志
     *               - current_execution_depth: 当前执行深度
     */
    public static function debug(): array
    {
        $debugInfo = HookManager::debug();

        // 添加更友好的格式化信息
        $hookStats = [];
        foreach ($debugInfo['handlers'] as $hookClass => $handlers) {
            $hookStats[$hookClass] = [
                'handler_count' => count($handlers),
                'handlers' => $handlers,
            ];
        }

        return [
            'hook_count' => count($debugInfo['registered_hooks']),
            'registered_hooks' => $debugInfo['registered_hooks'],
            'hook_stats' => $hookStats,
            'total_handler_instances' => $debugInfo['handler_instances'] ?? 0,
            'debug_mode' => $debugInfo['debug_mode'],
            'execution_log' => $debugInfo['execution_log'],
            'current_execution_depth' => $debugInfo['current_execution_depth'],
        ];
    }

    /**
     * 清理所有Hook和处理器
     *
     * 清除所有处理器、订阅者和执行日志
     */
    public static function clear(): void
    {
        HookManager::clear();
    }

    /**
     * 设置最大执行深度
     *
     * 防止Hook执行过程中的无限递归调用
     *
     * @param  int  $depth  最大执行深度，建议设置为10-50之间
     */
    public static function setMaxDepth(int $depth): void
    {
        HookManager::setMaxExecutionDepth($depth);
    }

    /**
     * 获取最大执行深度
     *
     * @return int 当前设置的最大执行深度
     */
    public static function getMaxDepth(): int
    {
        return HookManager::getMaxExecutionDepth();
    }

    /**
     * 注册Hook订阅者
     *
     * 订阅者可以自动注册多个Hook处理器，简化批量处理器的注册过程
     *
     * @param  string|HookSubscriberInterface  $subscriber  订阅者类名或实例
     */
    public static function subscribe(string|HookSubscriberInterface $subscriber): void
    {
        if (is_string($subscriber)) {
            // 传入的是类名
            HookManager::registerSubscriber($subscriber);
        } else {
            // 传入的是实例，获取类名
            HookManager::registerSubscriber(get_class($subscriber));
        }
    }

    /**
     * 批量注册订阅者
     *
     * @param  array<HookSubscriberInterface>  $subscribers  订阅者实例数组
     *
     * @throws InvalidArgumentException 当订阅者不是HookSubscriberInterface实例时抛出异常
     */
    public static function subscribeMany(array $subscribers): void
    {
        HookManager::registerSubscribers($subscribers);
    }

    /**
     * 取消订阅
     *
     * 移除指定订阅者及其注册的所有Hook处理器
     *
     * @param  string  $subscriberClass  订阅者类名
     */
    public static function unsubscribe(string $subscriberClass): void
    {
        HookManager::removeSubscriber($subscriberClass);
    }

    /**
     * 获取所有已注册的订阅者
     *
     * @return array<string> 订阅者类名列表
     */
    public static function getSubscribers(): array
    {
        return HookManager::getRegisteredSubscribers();
    }

    /**
     * 检查订阅者是否已注册
     *
     * @param  string  $subscriberClass  订阅者类名
     * @return bool 如果已注册返回true，否则返回false
     */
    public static function isSubscribed(string $subscriberClass): bool
    {
        return HookManager::isSubscriberRegistered($subscriberClass);
    }

    /**
     * 获取订阅者实例
     *
     * @param  string  $subscriberClass  订阅者类名
     * @return HookSubscriberInterface|null 订阅者实例，如果不存在返回null
     */
    public static function getSubscriber(string $subscriberClass): ?HookSubscriberInterface
    {
        return HookManager::getSubscriber($subscriberClass);
    }

    /**
     * 获取所有已注册的Hook及其处理器
     *
     * 返回按Hook类名组织的处理器列表，每个处理器包含元数据信息
     *
     * @return array Hook列表，格式: [hookClass => [处理器对象数组]]
     */
    public static function all(): array
    {
        $debugInfo = HookManager::debug();
        $hooks = [];

        foreach ($debugInfo['handlers'] as $hookClass => $handlers) {
            $hooks[$hookClass] = [];

            foreach ($handlers as $handlerInfo) {
                // 创建处理器代理对象，包含必要的信息
                $hooks[$hookClass][] = new HookHandlerProxy(
                    $handlerInfo['handler'],
                    $handlerInfo['priority'],
                    $handlerInfo['type']
                );
            }
        }

        return $hooks;
    }

    /**
     * 清理所有订阅者
     *
     * 移除所有已注册的订阅者及其处理器
     */
    public static function clearSubscribers(): void
    {
        HookManager::clearSubscribers();
    }
}
