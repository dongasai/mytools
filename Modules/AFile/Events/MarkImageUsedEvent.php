<?php

namespace Modules\AFile\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * 标记图片已使用事件
 */
class MarkImageUsedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * 图片ID
     *
     * @var int
     */
    public int $imageId;

    /**
     * 关联类型
     *
     * @var string
     */
    public string $reType;

    /**
     * 关联ID
     *
     * @var int
     */
    public int $reId;

    /**
     * 创建一个新的事件实例
     *
     * @param  int  $imageId  图片ID
     * @param  string  $reType  关联类型（可选，默认''）
     * @param  int  $reId  关联ID（可选，默认0）
     * @return void
     */
    public function __construct(int $imageId, string $reType = '', int $reId = 0)
    {
        $this->imageId = $imageId;
        $this->reType = $reType;
        $this->reId = $reId;
    }
}