<?php

declare(strict_types=1);

namespace Modules\Notification\Services;

use Modules\Notification\Contracts\NotificationHookInterface;
use Illuminate\Support\Collection;

/**
 * 通知钩子管理器.
 *
 * 负责管理通知发送生命周期的钩子执行，支持发送前、发送后和错误处理钩子。
 */
class HookManager
{
    /**
     * 注册的钩子集合.
     */
    protected static Collection $hooks;

    /**
     * 注册通知钩子.
     *
     * @param NotificationHookInterface $hook 钩子实例
     */
    public static function register(NotificationHookInterface $hook): void
    {
        if (!isset(self::$hooks)) {
            self::$hooks = Collection::make();
        }
        self::$hooks->push($hook);
    }

    /**
     * 执行发送前钩子.
     *
     * 在通知发送前执行所有注册的钩子，可用于修改通知数据。
     *
     * @param array<string, mixed> $data 通知数据
     * @return array<string, mixed> 处理后的通知数据
     */
    public static function executeBeforeSend(array $data): array
    {
        if (!isset(self::$hooks)) {
            return $data;
        }

        foreach (self::$hooks as $hook) {
            $data = $hook->beforeSend($data);
        }

        return $data;
    }

    /**
     * 执行发送后钩子.
     *
     * 在通知发送后执行所有注册的钩子，可用于记录日志或触发后续操作。
     *
     * @param array<string, mixed> $data 通知数据
     * @param bool $result 发送结果
     */
    public static function executeAfterSend(array $data, bool $result): void
    {
        if (!isset(self::$hooks)) {
            return;
        }

        foreach (self::$hooks as $hook) {
            $hook->afterSend($data, $result);
        }
    }

    /**
     * 执行错误钩子.
     *
     * 在通知发送失败时执行所有注册的钩子，可用于错误处理和通知。
     *
     * @param array<string, mixed> $data 通知数据
     * @param \Exception $exception 异常实例
     */
    public static function executeOnError(array $data, \Exception $exception): void
    {
        if (!isset(self::$hooks)) {
            return;
        }

        foreach (self::$hooks as $hook) {
            $hook->onError($data, $exception);
        }
    }
}