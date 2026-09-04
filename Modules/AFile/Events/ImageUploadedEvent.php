<?php

namespace Modules\AFile\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\AFile\Models\FileImg;

/**
 * 图片上传事件
 */
class ImageUploadedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * 图片模型
     */
    public FileImg $image;

    /**
     * 创建一个新的事件实例
     *
     * @return void
     */
    public function __construct(FileImg $image)
    {
        $this->image = $image;
    }
}
