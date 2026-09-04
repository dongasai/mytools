<?php

declare(strict_types=1);

namespace Modules\ABase\Hooks\Core;

/**
 * Hook处理器基类
 *
 * 提供Hook处理器的通用实现，所有具体的Handler类都应该继承此类
 */
abstract class CoreHookHandler implements HookHandlerInterface
{
    /**
     * 处理器优先级
     */
    protected static int $priority = 50;

    /**
     * 获取处理器优先级
     */
    public static function getPriority(): int
    {
        return static::$priority;
    }

    /**
     * 设置处理器优先级
     */
    protected static function setPriority(int $priority): void
    {
        static::$priority = $priority;
    }

    /**
     * 检查处理器是否应该执行
     *
     * 默认实现：总是执行
     * 子类可以重写此方法实现自定义逻辑
     */
    public static function shouldExecute(HookParameterInterface $parameter, HookResultInterface $result): bool
    {
        return true;
    }

    /**
     * 处理Hook的核心方法
     *
     * 子类必须实现此方法
     */
    abstract public static function handle(HookParameterInterface $parameter, HookResultInterface $result): HookResultInterface;

    /**
     * 记录处理日志
     */
    protected static function log(string $message, array $context = []): void
    {
        if (function_exists('logger')) {
            logger()->info($message, array_merge([
                'handler' => static::class,
                'priority' => static::$priority,
            ], $context));
        }
    }

    /**
     * 记录错误日志
     */
    protected static function logError(string $message, array $context = []): void
    {
        if (function_exists('logger')) {
            logger()->error($message, array_merge([
                'handler' => static::class,
                'priority' => static::$priority,
            ], $context));
        }
    }

    /**
     * 记录调试日志
     */
    protected static function logDebug(string $message, array $context = []): void
    {
        if (function_exists('logger') && config('app.debug')) {
            logger()->debug($message, array_merge([
                'handler' => static::class,
                'priority' => static::$priority,
            ], $context));
        }
    }

    /**
     * 获取处理器的简短名称
     */
    protected static function getHandlerName(): string
    {
        $className = static::class;

        return substr($className, strrpos($className, '\\') + 1);
    }
}
