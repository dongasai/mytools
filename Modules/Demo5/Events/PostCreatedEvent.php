<?php

namespace Modules\Demo5\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Demo5\Models\Demo5Post;

/**
 * 文章创建事件
 *
 * 当新文章被创建时触发此事件
 * 可用于发送通知、更新缓存、记录日志等
 */
class PostCreatedEvent
{
    use Dispatchable, SerializesModels;

    public Demo5Post $post;

    public array $data;

    public ?int $userId;

    /**
     * 创建新的事件实例
     *
     * @param  Demo5Post  $post  创建的文章模型
     * @param  array  $data  创建时使用的数据
     * @param  int|null  $userId  操作用户ID
     */
    public function __construct(Demo5Post $post, array $data = [], ?int $userId = null)
    {
        $this->post = $post;
        $this->data = $data;
        $this->userId = $userId;
    }

    /**
     * 获取文章ID
     */
    public function getPostId(): int
    {
        return $this->post->id;
    }

    /**
     * 获取文章标题
     */
    public function getPostTitle(): string
    {
        return $this->post->title;
    }

    /**
     * 获取文章状态
     */
    public function getPostStatus(): string
    {
        return $this->post->status;
    }

    /**
     * 检查文章是否已发布
     */
    public function isPublished(): bool
    {
        return $this->post->status === 'published';
    }

    /**
     * 获取操作用户ID
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * 获取创建时间
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->post->created_at;
    }

    /**
     * 获取事件描述
     */
    public function getDescription(): string
    {
        $status = $this->getPostStatus() === 'published' ? '发布' : '创建';

        return "文章《{$this->getPostTitle()}》已{$status}";
    }

    /**
     * 获取日志数据
     */
    public function getLogData(): array
    {
        return [
            'event' => 'post.created',
            'post_id' => $this->getPostId(),
            'post_title' => $this->getPostTitle(),
            'post_status' => $this->getPostStatus(),
            'user_id' => $this->getUserId(),
            'created_at' => $this->getCreatedAt()->toDateTimeString(),
            'description' => $this->getDescription(),
        ];
    }
}
