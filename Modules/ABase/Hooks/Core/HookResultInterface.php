<?php

declare(strict_types=1);

namespace Modules\ABase\Hooks\Core;

/**
 * Hook结果接口
 *
 * 定义所有Hook返回值必须实现的方法
 */
interface HookResultInterface
{
    /**
     * 检查处理是否成功
     */
    public function isSuccess(): bool;

    /**
     * 检查是否处理失败
     */
    public function isFailure(): bool;

    /**
     * 检查是否有错误
     */
    public function hasErrors(): bool;

    /**
     * 获取错误信息
     */
    public function getErrors(): array;

    /**
     * 添加错误信息
     */
    public function addError(string $error): self;

    /**
     * 获取处理消息
     */
    public function getMessage(): string;

    /**
     * 设置处理消息
     */
    public function setMessage(string $message): self;

    /**
     * 转换为数组
     */
    public function toArray(): array;
}
