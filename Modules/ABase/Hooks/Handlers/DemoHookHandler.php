<?php

declare(strict_types=1);

namespace Modules\ABase\Hooks\Handlers;

use Modules\ABase\Hooks\Core\CoreHookHandler;
use Modules\ABase\Hooks\Parameters\DemoHookParameter;
use Modules\ABase\Hooks\Results\DemoHookResult;

/**
 * DemoHook处理器
 *
 * 演示Hook处理器的基本实现，展示如何使用CoreHookHandler基类
 */
class DemoHookHandler extends CoreHookHandler
{
    // 默认配置
    private static string $defaultPrefix = 'demo';

    private static int $multiplier = 2;

    private static bool $enableLogging = true;

    /**
     * 配置处理器参数
     */
    public static function configure(string $defaultPrefix = 'demo', int $multiplier = 2, bool $enableLogging = true): void
    {
        self::$defaultPrefix = $defaultPrefix;
        self::$multiplier = $multiplier;
        self::$enableLogging = $enableLogging;
    }

    /**
     * 获取处理器优先级
     */
    public static function getPriority(): int
    {
        return 20; // 设置演示处理器的优先级
    }

    /**
     * 处理DemoHook - 接口兼容版本
     */
    public static function handle(\Modules\ABase\Hooks\Core\HookParameterInterface $parameter, \Modules\ABase\Hooks\Core\HookResultInterface $result): \Modules\ABase\Hooks\Core\HookResultInterface
    {
        return static::processDemoHook($parameter, $result);
    }

    /**
     * 处理DemoHook - 强类型版本
     *
     * 这是实际的处理器实现，使用强类型约束
     */
    public static function processDemoHook(DemoHookParameter $parameter, DemoHookResult $result): DemoHookResult
    {
        // 获取参数
        $name = $parameter->getName();
        $value = $parameter->getValue();
        $options = $parameter->getOptions();
        $enabled = $parameter->isEnabled();

        static::logDebug('开始处理DemoHook', [
            'name' => $name,
            'value' => $value,
            'enabled' => $enabled,
        ]);

        if (! $enabled) {
            return DemoHookResult::failure(
                'DemoHook已禁用',
                (string) time(),
                'Hook processing skipped because disabled'
            );
        }

        // 处理逻辑：添加前缀并乘以倍数
        $processedName = static::$defaultPrefix . '_' . $name;
        $processedValue = $value * static::$multiplier;

        // 应用选项
        $appliedOptions = array_merge([
            'handler' => static::class,
            'processed_at' => time(),
        ], $options);

        static::log('DemoHook处理完成', [
            'original_name' => $name,
            'processed_name' => $processedName,
            'original_value' => $value,
            'processed_value' => $processedValue,
        ]);

        return DemoHookResult::success(
            $processedName,
            $processedValue,
            $appliedOptions,
            (string) time(),
            'DemoHook processed successfully'
        );
    }
}
