<?php

declare(strict_types=1);

namespace Modules\ABase\Hooks\Management;

use Modules\ABase\Hooks\Core\HookDefinition;
use Modules\ABase\Hooks\Core\HookHandlerInterface;
use Modules\ABase\Hooks\Core\HookParameter;
use Modules\ABase\Hooks\Core\HookResult;
use Modules\ABase\Hooks\Core\HookSubscriberInterface;
use Closure;
use Exception;
use InvalidArgumentException;

/**
 * Hook核心管理器
 *
 * 负责Hook处理器的管理、Hook的执行和调试
 * 采用注册验证模式，只有注册过的Hook才能被使用
 */
class HookManager
{
    /**
     * 已注册的Hook定义
     * 结构: [hookClassName => HookDefinition实例]
     */
    private static array $registeredHooks = [];

    /**
     * 处理器存储
     * 结构: [hookClass => [priority => [handlerClass, ...]]]
     */
    private static array $handlers = [];

    /**
     * 处理器排序状态
     * 结构: [hookClass => bool] 标记该hook的处理器是否已排序
     */
    private static array $handlersSorted = [];

    // 处理器实例现在直接存储在$handlers数组中，无需单独缓存

    /**
     * Hook订阅者存储
     */
    private static array $subscribers = [];

    // 订阅者实例仍然需要缓存，因为订阅者方法不是静态的
    private static array $subscriberInstances = [];

    /**
     * 执行日志
     */
    private static array $executionLog = [];

    /**
     * 调试模式
     */
    private static bool $debugMode = false;

    /**
     * 最大执行深度（防止无限循环）
     */
    private static int $maxExecutionDepth = 50;

    /**
     * 当前执行深度
     */
    private static int $currentExecutionDepth = 0;

    /**
     * 移除Hook及其所有处理器
     */
    public static function removeHook(string $hookClass): void
    {
        unset(self::$handlers[$hookClass]);
        unset(self::$handlersSorted[$hookClass]);

        if (self::$debugMode) {
            self::logDebug("Hook removed: {$hookClass}");
        }
    }

    /**
     * 获取所有已使用的Hook类名
     */
    public static function getRegisteredHooks(): array
    {
        return array_keys(self::$handlers);
    }

    /**
     * 获取所有已注册的Hook定义实例
     */
    public static function getRegisteredHookDefinitions(): array
    {
        return self::$registeredHooks;
    }

    /**
     * 检查Hook是否有处理器
     */
    public static function isHookRegistered(string $hookClass): bool
    {
        return isset(self::$handlers[$hookClass]);
    }

    /**
     * 注册Hook定义
     *
     * 只有注册过的Hook才能添加处理器和执行
     *
     * @param  class-string<HookDefinition>  $hookClass  Hook定义类名
     *
     * @throws InvalidArgumentException 当Hook类不存在或不继承HookDefinition时
     */
    public static function registerHook(string $hookClass): void
    {
        if (! class_exists($hookClass)) {
            throw new InvalidArgumentException("Hook class '{$hookClass}' does not exist");
        }

        if (! is_subclass_of($hookClass, HookDefinition::class)) {
            throw new InvalidArgumentException("Hook class '{$hookClass}' must extend HookDefinition");
        }

        // 创建Hook定义实例并存储
        self::$registeredHooks[$hookClass] = new $hookClass;

        if (self::$debugMode) {
            self::logDebug("Hook registered: {$hookClass}");
        }
    }

    /**
     * 检查Hook是否已注册
     *
     * @param  string  $hookClass  Hook类名
     */
    public static function isHookDefinitionRegistered(string $hookClass): bool
    {
        return isset(self::$registeredHooks[$hookClass]);
    }

    /**
     * 获取已注册的Hook定义
     *
     * @param  string  $hookClass  Hook类名
     */
    public static function getRegisteredHook(string $hookClass): ?HookDefinition
    {
        return self::$registeredHooks[$hookClass] ?? null;
    }

    /**
     * 获取所有已注册的Hook定义
     *
     * @return array<string, HookDefinition>
     */
    public static function getAllRegisteredHooks(): array
    {
        return self::$registeredHooks;
    }

    /**
     * 验证Hook是否已注册
     *
     * @param  string  $hookClass  Hook类名
     *
     * @throws InvalidArgumentException 当Hook未注册时
     */
    private static function validateHookRegistered(string $hookClass): void
    {
        if (! self::isHookDefinitionRegistered($hookClass)) {
            throw new InvalidArgumentException(
                "Hook '{$hookClass}' is not registered. " .
                    'Only registered hooks can be used. ' .
                    "Register the hook first using HookManager::registerHook('{$hookClass}')."
            );
        }
    }

    /**
     * 添加Hook处理器
     *
     * 为指定的Hook类注册处理器。支持多种处理器类型：类名、闭包或数组格式。
     * 处理器将按照排序值排序执行，排序值数字越小越早执行。
     *
     * @param  class-string<HookDefinition>  $hookClass  Hook定义类的完整类名
     * @param  string|Closure|array  $handler  处理器，支持以下格式：
     *                                         - string: 处理器类名（需实现HookHandlerInterface）
     *                                         - Closure: 闭包函数
     *                                         - array: [类名, 方法名] 格式
     * @param  int  $sort  执行排序值，范围1-100，数字越小越早执行，默认为10
     *
     * @throws InvalidArgumentException 当Hook类不存在、处理器格式无效或排序值超出范围时
     *
     * @example 添加类处理器
     * HookManager::add(PostCreatedHook::class, PostCreatedHandler::class);
     * @example 添加闭包处理器
     * HookManager::add(PostCreatedHook::class, function($post) {
     *     logger()->info('Post created: ' . $post->title);
     * });
     * @example 添加数组格式处理器
     * HookManager::add(PostCreatedHook::class, [PostHandler::class, 'handleCreated']);
     * @example 带排序值的处理器
     * HookManager::add(PostCreatedHook::class, HighPriorityHandler::class, 1);
     */
    public static function add(string $hookClass, string|Closure|array $handler, int $sort = 10): void
    {
        // 验证Hook是否已注册
        self::validateHookRegistered($hookClass);

        // 验证排序值范围
        if ($sort < 1 || $sort > 100) {
            throw new InvalidArgumentException("Sort value must be between 1 and 100, {$sort} given");
        }

        if (! isset(self::$handlers[$hookClass])) {
            self::$handlers[$hookClass] = [];
        }

        if (! isset(self::$handlers[$hookClass][$sort])) {
            self::$handlers[$hookClass][$sort] = [];
        }

        // 添加新处理器后需要重新排序
        self::$handlersSorted[$hookClass] = false;

        // 处理不同类型的处理器
        if ($handler instanceof Closure) {
            // 闭包处理器
            self::$handlers[$hookClass][$sort][] = $handler;

            if (self::$debugMode) {
                self::logDebug("Closure handler added for {$hookClass} with sort {$sort}");
            }
        } elseif (is_array($handler) && count($handler) === 2) {
            // 可调用数组处理器 [$object, 'method']
            self::$handlers[$hookClass][$sort][] = $handler;

            if (self::$debugMode) {
                $objectClass = is_object($handler[0]) ? get_class($handler[0]) : $handler[0];
                self::logDebug("Callable array handler added: {$objectClass}::{$handler[1]} for {$hookClass} with sort {$sort}");
            }
        } elseif (is_string($handler)) {
            // 类处理器 - 静态类调用
            if (! class_exists($handler)) {
                throw new InvalidArgumentException("Handler class '{$handler}' does not exist");
            }

            if (! is_subclass_of($handler, HookHandlerInterface::class)) {
                throw new InvalidArgumentException("Handler '{$handler}' must implement HookHandlerInterface");
            }

            // 存储为静态调用数组
            self::$handlers[$hookClass][$sort][] = [$handler, 'handle'];

            if (self::$debugMode) {
                self::logDebug("Static handler added: {$handler}::handle for {$hookClass} with sort {$sort}");
            }
        } else {
            throw new InvalidArgumentException('Invalid handler type. Expected string, Closure, or callable array');
        }
    }

    /**
     * 批量添加处理器
     *
     * @param  class-string<HookDefinition>  $hookClass  Hook定义类的完整类名
     * @param  array  $handlers  处理器数组，支持以下格式：
     *                           - string: 处理器类名
     *                           - array: ['class' => 类名, 'priority' => 排序值(1-100)]
     *
     * @example 批量添加字符串处理器
     * HookManager::addHandlers(PostCreatedHook::class, [
     *     Handler1::class,
     *     Handler2::class,
     * ]);
     * @example 批量添加数组格式处理器
     * HookManager::addHandlers(PostCreatedHook::class, [
     *     ['class' => Handler1::class, 'priority' => 5],
     *     ['class' => Handler2::class, 'priority' => 50],
     * ]);
     */
    public static function addHandlers(string $hookClass, array $handlers): void
    {
        foreach ($handlers as $handlerData) {
            if (is_string($handlerData)) {
                self::add($hookClass, $handlerData);
            } elseif (is_array($handlerData)) {
                $handlerClass = $handlerData['class'] ?? null;
                $sort = $handlerData['priority'] ?? 10;

                if ($handlerClass) {
                    self::add($hookClass, $handlerClass, $sort);
                }
            }
        }
    }

    /**
     * 移除处理器
     */
    public static function removeHandler(string $hookClass, string|Closure|array $handler): void
    {
        if (! isset(self::$handlers[$hookClass])) {
            return;
        }

        foreach (self::$handlers[$hookClass] as $priority => $handlers) {
            foreach ($handlers as $key => $existingHandler) {
                if (
                    (is_string($handler) && $existingHandler === $handler) ||
                    ($handler instanceof Closure && $existingHandler === $handler) ||
                    (is_array($handler) && is_array($existingHandler) && $handler[0] === $existingHandler[0] && $handler[1] === $existingHandler[1])
                ) {
                    unset(self::$handlers[$hookClass][$priority][$key]);
                    // 移除处理器后需要重新排序
                    self::$handlersSorted[$hookClass] = false;

                    if (self::$debugMode) {
                        if (is_string($handler)) {
                            self::logDebug("Handler class removed: {$handler} from {$hookClass}");
                        } elseif ($handler instanceof Closure) {
                            self::logDebug("Closure handler removed from {$hookClass}");
                        } elseif (is_array($handler)) {
                            $objectClass = is_object($handler[0]) ? get_class($handler[0]) : $handler[0];
                            self::logDebug("Callable handler removed: {$objectClass}::{$handler[1]} from {$hookClass}");
                        }
                    }

                    return;
                }
            }
        }
    }

    /**
     * 获取Hook的所有处理器
     */
    public static function getHandlers(string $hookClass): array
    {
        if (! isset(self::$handlers[$hookClass])) {
            return [];
        }

        // 仅在未排序时执行排序，避免每次调用都重复排序
        if (empty(self::$handlersSorted[$hookClass])) {
            ksort(self::$handlers[$hookClass]);
            self::$handlersSorted[$hookClass] = true;
        }

        $result = [];
        foreach (self::$handlers[$hookClass] as $priority => $handlers) {
            foreach ($handlers as $handler) {
                if ($handler instanceof Closure) {
                    // 闭包处理器
                    $result[] = [
                        'type' => 'closure',
                        'handler' => $handler,
                        'priority' => $priority,
                    ];
                } elseif (is_array($handler) && count($handler) === 2) {
                    // 可调用数组处理器
                    $result[] = [
                        'type' => 'callable',
                        'handler' => $handler,
                        'priority' => $priority,
                        // 添加调用信息用于调试
                        'callable' => $handler[0],
                        'method' => $handler[1],
                    ];
                } else {
                    // 这种情况现在不应该出现，因为类处理器已经被转换为可调用数组
                    throw new InvalidArgumentException('Invalid handler format found');
                }
            }
        }

        return $result;
    }

    /**
     * 检查是否有处理器
     */
    public static function hasHandlers(string $hookClass): bool
    {
        return ! empty(self::$handlers[$hookClass]);
    }

    /**
     * 应用Hook
     */
    public static function apply(string $hookClass, HookParameter $parameter, ?HookResult $initialResult = null): HookResult
    {
        // 验证Hook是否已注册
        self::validateHookRegistered($hookClass);

        if (++self::$currentExecutionDepth > self::$maxExecutionDepth) {
            throw new Exception('Maximum hook execution depth exceeded - possible infinite loop');
        }

        try {
            $handlers = self::getHandlers($hookClass);

            if (empty($handlers)) {
                throw new InvalidArgumentException("No handlers registered for hook: {$hookClass}");
            }

            // 获取Hook定义以检查是否为单处理器Hook
            $hookDefinition = self::getRegisteredHook($hookClass);
            $isSingleProcessor = $hookDefinition->isSingleProcessor();

            // 创建初始结果（如果未提供）
            if ($initialResult === null) {
                $initialResult = $hookDefinition->createSuccessResult([], '');
            }

            $currentResult = $initialResult;
            $executedHandlers = [];
            $hasValidResult = false;

            foreach ($handlers as $handlerData) {
                // 如果是单处理器Hook且已有有效结果，则停止执行后续处理器
                if ($isSingleProcessor && $hasValidResult) {
                    if (self::$debugMode) {
                        self::logDebug("Single processor hook '{$hookClass}' stopped: valid result already obtained");
                    }
                    break;
                }
                try {
                    if (self::$debugMode) {
                        $startTime = microtime(true);
                    }

                    if ($handlerData['type'] === 'closure') {
                        // 闭包处理器
                        $handler = $handlerData['handler'];
                        $handlerClass = 'Closure';

                        // 执行闭包处理器
                        $currentResult = $handler($parameter, $currentResult);

                        $executedHandlers[] = $handlerClass;

                        // 单处理器Hook检查：如果结果有效，标记为已有有效结果
                        if ($isSingleProcessor && $hookDefinition->isValidSingleProcessorResult($currentResult)) {
                            $hasValidResult = true;
                            if (self::$debugMode) {
                                self::logDebug("Single processor hook '{$hookClass}' found valid result from closure handler");
                            }
                        }

                        if (self::$debugMode) {
                            $executionTime = (microtime(true) - $startTime) * 1000;
                            self::logDebug('Closure handler executed in ' . number_format($executionTime, 2) . 'ms');
                        }
                    } elseif ($handlerData['type'] === 'callable') {
                        // 可调用数组处理器（统一处理）
                        $handler = $handlerData['handler'];
                        $callable = $handlerData['callable'];
                        $method = $handlerData['method'];

                        // 确定调用类型和类名
                        if (is_string($callable)) {
                            // 静态类调用
                            $callableClass = $callable;

                            // 如果是HookHandler，检查shouldExecute静态方法
                            if (is_subclass_of($callable, HookHandlerInterface::class)) {
                                if (! $callableClass::shouldExecute($parameter, $currentResult)) {
                                    if (self::$debugMode) {
                                        self::logDebug("Static handler skipped: {$callableClass}::{$method} (shouldExecute returned false)");
                                    }

                                    continue;
                                }
                            }
                        } else {
                            // 实例方法调用
                            $callableClass = is_object($callable) ? get_class($callable) : $callable;
                        }

                        // 执行处理器
                        $currentResult = $handler($parameter, $currentResult);

                        $executedHandlers[] = $callableClass . '::' . $method;

                        // 单处理器Hook检查：如果结果有效，标记为已有有效结果
                        if ($isSingleProcessor && $hookDefinition->isValidSingleProcessorResult($currentResult)) {
                            $hasValidResult = true;
                            if (self::$debugMode) {
                                self::logDebug("Single processor hook '{$hookClass}' found valid result from {$callableClass}::{$method}");
                            }
                        }

                        if (self::$debugMode) {
                            $executionTime = (microtime(true) - $startTime) * 1000;
                            self::logDebug("Callable handler executed: {$callableClass}::{$method} in " . number_format($executionTime, 2) . 'ms');
                        }
                    } else {
                        // 这种情况现在不应该出现
                        throw new InvalidArgumentException("Unknown handler type: {$handlerData['type']}");
                    }
                } catch (Exception $e) {
                    if ($handlerData['type'] === 'closure') {
                        $handlerClass = 'Closure';
                    } elseif ($handlerData['type'] === 'callable') {
                        $callable = $handlerData['callable'];
                        $method = $handlerData['method'];
                        $callableClass = is_string($callable) ? $callable : (is_object($callable) ? get_class($callable) : $callable);
                        $handlerClass = $callableClass . '::' . $method;
                    } else {
                        $handlerClass = 'Unknown';
                    }
                    $errorMessage = "Handler '{$handlerClass}' execution failed: " . $e->getMessage();

                    if (self::$debugMode) {
                        self::logDebug($errorMessage);
                    }

                    // 记录错误但不中断执行
                    $currentResult->addError($errorMessage);
                }
            }

            if (self::$debugMode) {
                $debugMessage = "Hook '{$hookClass}' executed with " . count($executedHandlers) . ' handlers';
                if ($isSingleProcessor) {
                    $debugMessage .= ' (single processor mode';
                    if ($hasValidResult) {
                        $debugMessage .= ', valid result obtained)';
                    } else {
                        $debugMessage .= ', no valid result)';
                    }
                }
                self::logDebug($debugMessage);
            }

            self::$currentExecutionDepth--;

            return $currentResult;
        } catch (Exception $e) {
            self::$currentExecutionDepth--;
            throw $e;
        }
    }

    /**
     * 启用调试模式
     */
    public static function enableDebug(): void
    {
        self::$debugMode = true;
    }

    /**
     * 禁用调试模式
     */
    public static function disableDebug(): void
    {
        self::$debugMode = false;
    }

    /**
     * 检查调试模式是否启用
     */
    public static function isDebugEnabled(): bool
    {
        return self::$debugMode;
    }

    /**
     * 记录调试信息
     */
    private static function logDebug(string $message): void
    {
        $timestamp = date('Y-m-d H:i:s');
        self::$executionLog[] = "[{$timestamp}] {$message}";
    }

    /**
     * 获取执行日志
     */
    public static function getExecutionLog(): array
    {
        return self::$executionLog;
    }

    /**
     * 清空执行日志
     */
    public static function clearExecutionLog(): void
    {
        self::$executionLog = [];
    }

    /**
     * 获取调试信息
     */
    public static function debug(): array
    {
        $handlerCount = 0;
        $flattenedHandlers = [];

        foreach (self::$handlers as $hookClass => $hookHandlers) {
            $flattenedHandlers[$hookClass] = [];
            foreach ($hookHandlers as $priority => $priorityHandlers) {
                foreach ($priorityHandlers as $handler) {
                    $handlerCount++;
                    $handlerName = is_string($handler) ? $handler : (is_array($handler) ?
                            (is_object($handler[0]) ? get_class($handler[0]) : $handler[0]) . '::' . $handler[1] :
                            'Closure');

                    $flattenedHandlers[$hookClass][] = [
                        'handler' => $handlerName,
                        'priority' => $priority,
                        'type' => is_string($handler) ? 'static' : (is_array($handler) ? 'callable' : 'closure')
                    ];
                }
            }
        }

        // 获取订阅者信息
        $subscriberInfo = [];
        foreach (self::$subscribers as $hookClass => $subscriberMethods) {
            $subscriberInfo[$hookClass] = [];
            foreach ($subscriberMethods as $method) {
                $subscriberInfo[$hookClass][] = [
                    'subscriber' => $method['subscriber'] ?? 'unknown',
                    'method' => $method['method'] ?? 'unknown',
                    'instance' => isset(self::$subscriberInstances[$method['subscriber']]) ? get_class(self::$subscriberInstances[$method['subscriber']]) : null
                ];
            }
        }

        // 计算实际的处理器总数
        $actualHandlerCount = 0;
        foreach ($flattenedHandlers as $handlers) {
            $actualHandlerCount += count($handlers);
        }

        return [
            'registered_hooks' => array_keys(self::$handlers),
            'handlers' => $flattenedHandlers,
            'subscribers' => $subscriberInfo,
            'subscriber_instances' => array_map(fn($instance) => get_class($instance), self::$subscriberInstances),
            'execution_log' => self::$executionLog,
            'debug_mode' => self::$debugMode,
            'total_handlers' => $actualHandlerCount,
            'total_subscribers' => count(self::$subscriberInstances),
            'current_execution_depth' => self::$currentExecutionDepth,
        ];
    }

    /**
     * 清理所有数据
     */
    public static function clear(): void
    {
        self::$handlers = [];
        self::$handlersSorted = [];
        self::$subscribers = [];
        self::$subscriberInstances = [];
        self::$executionLog = [];
        self::$currentExecutionDepth = 0;
    }

    /**
     * 设置最大执行深度
     */
    public static function setMaxExecutionDepth(int $depth): void
    {
        self::$maxExecutionDepth = $depth;
    }

    /**
     * 获取最大执行深度
     */
    public static function getMaxExecutionDepth(): int
    {
        return self::$maxExecutionDepth;
    }

    /**
     * 注册Hook订阅者
     *
     * @param  string  $subscriberClass  订阅者类名
     *
     * @throws InvalidArgumentException
     */
    public static function registerSubscriber(string|HookSubscriberInterface $subscriber): void
    {
        // 处理字符串（类名）或实例
        if (is_string($subscriber)) {
            $subscriberClass = $subscriber;

            if (isset(self::$subscribers[$subscriberClass])) {
                if (self::$debugMode) {
                    self::logDebug("Subscriber already registered: {$subscriberClass}");
                }

                return;
            }

            // 检查类是否存在
            if (! class_exists($subscriberClass)) {
                throw new InvalidArgumentException("Subscriber class '{$subscriberClass}' does not exist");
            }

            // 检查是否实现了正确的接口
            if (! is_subclass_of($subscriberClass, HookSubscriberInterface::class)) {
                throw new InvalidArgumentException("Subscriber class '{$subscriberClass}' must implement HookSubscriberInterface");
            }

            // 创建订阅者实例，使用Laravel的依赖注入容器
            $subscriber = app($subscriberClass);
        } elseif ($subscriber instanceof HookSubscriberInterface) {
            $subscriberClass = get_class($subscriber);

            if (isset(self::$subscribers[$subscriberClass])) {
                if (self::$debugMode) {
                    self::logDebug("Subscriber already registered: {$subscriberClass}");
                }

                return;
            }
        } else {
            throw new InvalidArgumentException('Subscriber must be a class name or instance of HookSubscriberInterface');
        }

        // 调用订阅者的subscribe方法
        $subscriptions = $subscriber->subscribe();

        if (empty($subscriptions)) {
            throw new InvalidArgumentException("Subscriber '{$subscriberClass}' has no hook subscriptions");
        }

        // 注册订阅的Hook处理器
        foreach ($subscriptions as $hookClass => $handlerConfig) {
            if (is_string($handlerConfig)) {
                // 方法名字符串，转换为可调用数组
                $handler = [$subscriber, $handlerConfig];

                // 验证方法是否存在
                if (! method_exists($subscriber, $handlerConfig)) {
                    throw new InvalidArgumentException("Handler method '{$handlerConfig}' not found in subscriber '{$subscriberClass}'");
                }

                // 注册处理器
                self::add($hookClass, $handler, 10);
            } elseif ($handlerConfig instanceof Closure) {
                // 闭包处理器
                self::add($hookClass, $handlerConfig, 10);
            } elseif (is_array($handlerConfig) && count($handlerConfig) === 2) {
                // 可调用数组处理器
                self::add($hookClass, $handlerConfig, 10);
            } else {
                throw new InvalidArgumentException("Invalid handler configuration for hook '{$hookClass}' in subscriber '{$subscriberClass}'");
            }
        }

        self::$subscribers[$subscriberClass] = $subscriber;

        if (self::$debugMode) {
            self::logDebug("Subscriber registered: {$subscriberClass} with " . count($subscriptions) . ' hooks');
        }
    }

    /**
     * 批量注册订阅者
     *
     * @param  array  $subscribers  订阅者类名数组或实例数组
     */
    public static function registerSubscribers(array $subscribers): void
    {
        foreach ($subscribers as $subscriber) {
            if (is_string($subscriber)) {
                // 类名字符串
                self::registerSubscriber($subscriber);
            } elseif ($subscriber instanceof HookSubscriberInterface) {
                // 订阅者实例，获取类名
                self::registerSubscriber(get_class($subscriber));
            } else {
                throw new InvalidArgumentException('All subscribers must be class names or instances of HookSubscriberInterface');
            }
        }
    }

    /**
     * 移除Hook订阅者
     *
     * @param  string  $subscriberClass  订阅者类名
     */
    public static function removeSubscriber(string $subscriberClass): void
    {
        if (! isset(self::$subscribers[$subscriberClass])) {
            return;
        }

        // 移除相关的处理器
        $subscriber = self::$subscribers[$subscriberClass];
        $subscriptions = $subscriber->subscribe();

        foreach ($subscriptions as $hookClass => $handlerConfig) {
            if (is_string($handlerConfig)) {
                // 方法名字符串，创建可调用数组来查找
                $handler = [$subscriber, $handlerConfig];
                self::removeHandler($hookClass, $handler);
            } elseif ($handlerConfig instanceof Closure || (is_array($handlerConfig) && count($handlerConfig) === 2)) {
                // 闭包或可调用数组
                self::removeHandler($hookClass, $handlerConfig);
            }
        }

        unset(self::$subscribers[$subscriberClass]);

        if (self::$debugMode) {
            self::logDebug("Subscriber removed: {$subscriberClass}");
        }
    }

    /**
     * 获取所有已注册的订阅者
     *
     * @return array 订阅者列表
     */
    public static function getRegisteredSubscribers(): array
    {
        return array_keys(self::$subscribers);
    }

    /**
     * 检查订阅者是否已注册
     *
     * @param  string  $subscriberClass  订阅者类名
     */
    public static function isSubscriberRegistered(string $subscriberClass): bool
    {
        return isset(self::$subscribers[$subscriberClass]);
    }

    /**
     * 获取订阅者实例
     *
     * @param  string  $subscriberClass  订阅者类名
     */
    public static function getSubscriber(string $subscriberClass): ?HookSubscriberInterface
    {
        return self::$subscribers[$subscriberClass] ?? null;
    }

    /**
     * 清理所有订阅者
     */
    public static function clearSubscribers(): void
    {
        self::$subscribers = [];

        if (self::$debugMode) {
            self::logDebug('All subscribers cleared');
        }
    }
}
