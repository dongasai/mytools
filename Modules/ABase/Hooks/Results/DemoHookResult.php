<?php

declare(strict_types=1);

namespace Modules\ABase\Hooks\Results;

use Modules\ABase\Hooks\Core\HookResult;

/**
 * DemoHook结果类
 *
 * 用于存储DemoHook处理的结果
 */
class DemoHookResult extends HookResult
{
    /**
     * 处理后的名称
     */
    public readonly string $processed_name;

    /**
     * 处理后的值
     */
    public readonly int $processed_value;

    /**
     * 应用的选项
     */
    public readonly array $applied_options;

    /**
     * 是否已被处理
     */
    public readonly bool $was_processed;

    /**
     * 处理时间
     */
    public readonly string $processing_time;

    /**
     * 构造函数
     *
     * @param  bool  $success  处理是否成功
     * @param  string  $message  处理消息
     * @param  string  $processed_name  处理后的名称
     * @param  int  $processed_value  处理后的值
     * @param  array  $applied_options  应用的选项
     * @param  bool  $was_processed  是否已被处理
     * @param  string  $processing_time  处理时间
     */
    public function __construct(
        bool $success = false,
        string $message = '',
        string $processed_name = '',
        int $processed_value = 0,
        array $applied_options = [],
        bool $was_processed = false,
        string $processing_time = ''
    ) {
        parent::__construct($success, $message);
        $this->processed_name = $processed_name;
        $this->processed_value = $processed_value;
        $this->applied_options = $applied_options;
        $this->was_processed = $was_processed;
        $this->processing_time = $processing_time;
    }

    /**
     * 创建成功的DemoHook结果
     */
    public static function success(
        string $processed_name,
        int $processed_value,
        array $applied_options = [],
        string $processing_time = '',
        string $message = 'DemoHook processed successfully'
    ): self {
        return new self(true, $message, $processed_name, $processed_value, $applied_options, true, $processing_time);
    }

    /**
     * 创建失败的DemoHook结果
     */
    public static function failure(
        string $error_message,
        string $processing_time = '',
        string $message = 'DemoHook processing failed'
    ): self {
        $result = new self(false, $message, '', 0, [], false, $processing_time);
        $result->addError($error_message);

        return $result;
    }

    /**
     * 获取处理后的名称
     */
    public function getProcessedName(): string
    {
        return $this->processed_name;
    }

    /**
     * 获取处理后的值
     */
    public function getProcessedValue(): int
    {
        return $this->processed_value;
    }

    /**
     * 获取应用的选项
     */
    public function getAppliedOptions(): array
    {
        return $this->applied_options;
    }

    /**
     * 是否已被处理
     */
    public function wasProcessed(): bool
    {
        return $this->was_processed;
    }

    /**
     * 获取处理时间
     */
    public function getProcessingTime(): string
    {
        return $this->processing_time;
    }

    /**
     * 转换为数组
     */
    public function toArray(): array
    {
        return [
            'processed_name' => $this->processed_name,
            'processed_value' => $this->processed_value,
            'applied_options' => $this->applied_options,
            'was_processed' => $this->was_processed,
            'processing_time' => $this->processing_time,
            'success' => $this->isSuccess(),
            'errors' => $this->getErrors(),
            'message' => $this->getMessage(),
        ];
    }

    /**
     * JSON序列化
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
