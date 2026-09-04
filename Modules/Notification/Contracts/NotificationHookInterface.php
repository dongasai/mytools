<?php

declare(strict_types=1);

namespace Modules\Notification\Contracts;

/**
 * 通知钩子接口.
 *
 * 定义通知发送生命周期的钩子方法，用于在通知发送前后执行自定义逻辑。
 */
interface NotificationHookInterface
{
    /**
     * 发送前钩子.
     *
     * 在通知发送前执行，可用于修改通知数据。
     *
     * @param array<string, mixed> $data 通知数据
     * @return array<string, mixed> 处理后的通知数据
     */
    public function beforeSend(array $data): array;

    /**
     * 发送后钩子.
     *
     * 在通知发送后执行，可用于记录日志或触发后续操作。
     *
     * @param array<string, mixed> $data 通知数据
     * @param bool $result 发送结果（true 表示成功，false 表示失败）
     * @return void
     */
    public function afterSend(array $data, bool $result): void;

    /**
     * 发送失败钩子.
     *
     * 在通知发送失败时执行，可用于错误处理和通知。
     *
     * @param array<string, mixed> $data 通知数据
     * @param \Exception $exception 异常实例
     * @return void
     */
    public function onError(array $data, \Exception $exception): void;
}