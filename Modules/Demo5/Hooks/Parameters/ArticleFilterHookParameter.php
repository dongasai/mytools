<?php

declare(strict_types=1);

namespace Modules\Demo5\Hooks\Parameters;

use Modules\ABase\Hooks\Core\HookParameter;

/**
 * ArticleFilterHook 参数类
 *
 * 用于传递文章过滤所需的参数
 */
class ArticleFilterHookParameter extends HookParameter
{
    /**
     * 构造函数
     *
     * @param string $title 文章标题
     * @param string $content 文章内容
     * @param int $author_id 作者ID
     */
    public function __construct(
        public readonly string $title = '',
        public readonly string $content = '',
        public readonly int $author_id = 0
    ) {
        parent::__construct();
    }

    /**
     * 获取文章标题
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * 获取文章内容
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * 获取作者ID
     */
    public function getAuthorId(): int
    {
        return $this->author_id;
    }

    /**
     * 验证参数
     */
    protected function validate(): void
    {
        if (empty($this->title)) {
            throw new \InvalidArgumentException('文章标题不能为空');
        }

        if ($this->author_id <= 0) {
            throw new \InvalidArgumentException('作者ID必须大于0');
        }
    }

    /**
     * 转换为数组
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'author_id' => $this->author_id,
        ];
    }

    /**
     * JSON序列化
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * 创建ArticleFilterHook参数实例
     */
    public static function create(
        string $title,
        string $content = '',
        int $author_id = 0
    ): self {
        return new self($title, $content, $author_id);
    }
}