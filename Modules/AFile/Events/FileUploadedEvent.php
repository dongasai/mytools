<?php

namespace Modules\AFile\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\AFile\Models\FileFile;

/**
 * 文件上传事件
 */
class FileUploadedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * 文件模型
     */
    public FileFile $file;

    /**
     * 创建一个新的事件实例
     *
     * @return void
     */
    public function __construct(FileFile $file)
    {
        $this->file = $file;
    }
}
