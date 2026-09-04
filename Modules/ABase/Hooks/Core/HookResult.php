<?php

declare(strict_types=1);

namespace Modules\ABase\Hooks\Core;

use JsonSerializable;

/**
 * Hook返回值基类
 *
 * 所有Hook返回值都必须继承此类，提供统一的结果接口和状态管理
 * 每个Hook都有其独特的返回值类型，确保类型安全和结果完整性
 */
abstract class HookResult implements HookResultInterface, JsonSerializable
{
    /**
     * 处理是否成功
     */
    protected bool $success = true;

    /**
     * 错误信息
     */
    protected array $errors = [];

    /**
     * 处理消息
     */
    protected string $message = '';

    /**
     * 处理标识：表示处理器是否实际处理了请求
     *
     * 这个字段用于单处理器Hook判断结果是否有效
     * true - 处理器成功处理了请求
     * false - 处理器无法处理或跳过了该请求
     */
    protected bool $processed = false;

    /**
     * 处理器标识：记录是哪个处理器处理的数据
     *
     * 用于调试和追踪，记录处理器的类名或标识符
     */
    protected string $processor = '';

    /**
     * 构造函数
     */
    public function __construct(bool $success = true, string $message = '', bool $processed = false, string $processor = '')
    {
        $this->success = $success;
        $this->message = $message;
        $this->processed = $processed;
        $this->processor = $processor;
    }

    /**
     * 检查处理是否成功
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * 检查是否处理失败
     */
    public function isFailure(): bool
    {
        return ! $this->success;
    }

    /**
     * 检查是否有错误
     */
    public function hasErrors(): bool
    {
        return ! empty($this->errors);
    }

    /**
     * 获取错误信息
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * 添加错误信息
     */
    public function addError(string $error): self
    {
        $this->errors[] = $error;
        $this->success = false;

        return $this;
    }

    /**
     * 添加多个错误信息
     */
    public function addErrors(array $errors): self
    {
        foreach ($errors as $error) {
            $this->addError((string) $error);
        }

        return $this;
    }

    /**
     * 清空错误信息
     */
    public function clearErrors(): self
    {
        $this->errors = [];

        return $this;
    }

    /**
     * 获取处理消息
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * 获取处理标识
     */
    public function isProcessed(): bool
    {
        return $this->processed;
    }

    /**
     * 设置处理标识
     */
    public function setProcessed(bool $processed): self
    {
        $this->processed = $processed;
        return $this;
    }

    /**
     * 获取处理器标识
     */
    public function getProcessor(): string
    {
        return $this->processor;
    }

    /**
     * 设置处理器标识
     */
    public function setProcessor(string $processor): self
    {
        $this->processor = $processor;
        return $this;
    }

    /**
     * 标记为已处理（便捷方法）
     */
    public function markAsProcessed(string $processor = ''): self
    {
        $this->processed = true;
        if (!empty($processor)) {
            $this->processor = $processor;
        }
        return $this;
    }

    /**
     * 标记为未处理（便捷方法）
     */
    public function markAsUnprocessed(): self
    {
        $this->processed = false;
        return $this;
    }

    /**
     * 设置处理消息
     */
    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * 设置成功状态
     */
    public function setSuccess(bool $success): self
    {
        $this->success = $success;

        return $this;
    }

    /**
     * 合并其他结果的错误
     */
    public function mergeErrors(self $result): self
    {
        $this->addErrors($result->getErrors());

        return $this;
    }

    /**
     * 转换为数组
     * 子类应该重写此方法实现具体的数组转换逻辑
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'errors' => $this->errors,
            'message' => $this->message,
        ];
    }

    /**
     * 转换为JSON字符串
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this, $options);
    }

    /**
     * 魔术方法：转换为字符串
     */
    public function __toString(): string
    {
        return $this->toJson();
    }
}
