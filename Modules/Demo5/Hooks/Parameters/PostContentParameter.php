<?php

declare(strict_types=1);

namespace Modules\Demo5\Hooks\Parameters;

use Modules\ABase\Hooks\Core\HookParameter;

/**
 * 文章内容参数
 *
 * 用于Hook系统的文章内容处理参数
 */
class PostContentParameter extends HookParameter
{
    public function __construct(
        public readonly string $content = '',
        public readonly ?int $postId = null,
        public readonly array $options = []
    ) {
        parent::__construct();
    }

    protected function validate(): void
    {
        if (! is_string($this->content)) {
            throw new \InvalidArgumentException('文章内容必须是字符串');
        }

        if ($this->postId !== null && (! is_int($this->postId) || $this->postId <= 0)) {
            throw new \InvalidArgumentException('文章ID必须是正整数');
        }

        if (! is_array($this->options)) {
            throw new \InvalidArgumentException('选项必须是数组');
        }
    }

    public static function create(string $content, ?int $postId = null, array $options = []): self
    {
        return new self($content, $postId, $options);
    }

    /**
     * 获取文章内容
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * 获取文章ID
     */
    public function getPostId(): ?int
    {
        return $this->postId;
    }

    /**
     * 获取选项
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * 获取指定选项
     */
    public function getOption(string $key, mixed $default = null): mixed
    {
        return $this->options[$key] ?? $default;
    }

    /**
     * 实现JsonSerializable接口
     */
    public function jsonSerialize(): array
    {
        return [
            'content' => $this->getContent(),
            'post_id' => $this->getPostId(),
            'options' => $this->getOptions(),
        ];
    }
}
