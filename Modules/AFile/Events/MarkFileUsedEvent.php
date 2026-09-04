<?php

namespace Modules\AFile\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * 标记文件已使用事件
 */
class MarkFileUsedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * 文件ID
     *
     * @var int
     */
    public int $fileId;

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
     * @param  int  $fileId  文件ID
     * @param  string  $reType  关联类型（可选，默认''）
     * @param  int  $reId  关联ID（可选，默认0）
     * @return void
     */
    public function __construct(int $fileId, string $reType = '', int $reId = 0)
    {
        $this->fileId = $fileId;
        $this->reType = $reType;
        $this->reId = $reId;
    }
}