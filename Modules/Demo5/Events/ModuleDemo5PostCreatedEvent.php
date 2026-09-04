<?php

namespace Modules\Demo5\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Demo5\Models\Demo5Post;

/**
 * Demo5文章创建跨模块事件
 *
 * 命名规则: Module{模块名}{事件名}Event = Module + Demo5 + PostCreated + Event
 * 用于跨模块通信，通知其他模块文章创建完成
 * 只传递核心信息(post)，不传递内部业务数据(data, userId)
 *
 * 监听此事件的其他模块应异步处理(ShouldQueue)
 */
class ModuleDemo5PostCreatedEvent
{
    use Dispatchable, SerializesModels;

    /**
     * 文章模型（核心信息）
     */
    public Demo5Post $post;

    /**
     * 创建一个新的事件实例
     *
     * @param Demo5Post $post 创建的文章模型
     */
    public function __construct(Demo5Post $post)
    {
        $this->post = $post;
    }

    /**
     * 获取文章ID
     *
     * @return int
     */
    public function getPostId(): int
    {
        return $this->post->id;
    }

    /**
     * 获取文章标题
     *
     * @return string
     */
    public function getPostTitle(): string
    {
        return $this->post->title;
    }
}