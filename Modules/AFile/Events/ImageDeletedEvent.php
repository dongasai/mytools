<?php

namespace Modules\AFile\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * 图片删除事件
 */
class ImageDeletedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * 图片ID
     */
    public int $imageId;

    /**
     * 图片路径
     */
    public string $imagePath;

    /**
     * 存储磁盘
     */
    public string $storageDisk;

    /**
     * 创建一个新的事件实例
     *
     * @return void
     */
    public function __construct(int $imageId, string $imagePath, string $storageDisk)
    {
        $this->imageId = $imageId;
        $this->imagePath = $imagePath;
        $this->storageDisk = $storageDisk;
    }
}
