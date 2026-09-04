<?php

declare(strict_types=1);

namespace Modules\Demo5\Hooks\Results;

use Modules\ABase\Hooks\Core\HookResult;

/**
 * ArticleFilterHook 结果类
 *
 * 用于返回文章过滤后的内容
 */
class ArticleFilterHookResult extends HookResult
{
    /**
     * 构造函数
     *
     * @param bool $success 是否成功
     * @param string $message 消息
     * @param string $filtered_title 过滤后的标题
     * @param string $filtered_content 过滤后的内容
     * @param int $author_id 作者ID
     * @param bool $was_processed 是否已被处理
     */
    public function __construct(
        bool $success = false,
        string $message = '',
        public readonly string $filtered_title = '',
        public readonly string $filtered_content = '',
        public readonly int $author_id = 0,
        public readonly bool $was_processed = false
    ) {
        parent::__construct($success, $message);
    }

    /**
     * 创建成功的ArticleFilterHook结果
     *
     * 支持两种调用方式：
     * 1. 直接调用：success(string $filtered_title, string $filtered_content, int $author_id, string $message)
     * 2. 通过HookDefinition调用：success(array $data, string $message)
     */
    public static function success(
        string|array $filtered_title_or_data,
        string $filtered_content_or_message = '',
        int $author_id = 0,
        string $message = '文章内容过滤成功'
    ): self {
        // 判断是数组参数还是直接参数
        if (is_array($filtered_title_or_data)) {
            // 通过 HookDefinition::createSuccessResult 调用
            $data = $filtered_title_or_data;
            $message = $filtered_content_or_message;

            $filtered_title = $data['filtered_title'] ?? '';
            $filtered_content = $data['filtered_content'] ?? '';
            $author_id = $data['author_id'] ?? 0;
        } else {
            // 直接调用
            $filtered_title = $filtered_title_or_data;
            $filtered_content = $filtered_content_or_message;
        }

        $result = new self(true, $message, $filtered_title, $filtered_content, $author_id, true);
        return $result->markAsProcessed(self::class);
    }

    /**
     * 创建失败的ArticleFilterHook结果
     */
    public static function failure(
        string $error_message,
        string $message = '文章内容过滤失败'
    ): self {
        $result = new self(false, $message);
        $result->addError($error_message);

        return $result;
    }

    /**
     * 获取过滤后的标题
     */
    public function getFilteredTitle(): string
    {
        return $this->filtered_title;
    }

    /**
     * 获取过滤后的内容
     */
    public function getFilteredContent(): string
    {
        return $this->filtered_content;
    }

    /**
     * 获取作者ID
     */
    public function getAuthorId(): int
    {
        return $this->author_id;
    }

    /**
     * 是否已被处理
     */
    public function wasProcessed(): bool
    {
        return $this->was_processed;
    }

    /**
     * 转换为数组
     */
    public function toArray(): array
    {
        return [
            'filtered_title' => $this->filtered_title,
            'filtered_content' => $this->filtered_content,
            'author_id' => $this->author_id,
            'was_processed' => $this->was_processed,
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