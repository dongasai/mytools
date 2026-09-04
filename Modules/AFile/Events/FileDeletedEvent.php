<?php

namespace Modules\AFile\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * 文件删除事件
 */
class FileDeletedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * 文件ID
     */
    public int $fileId;

    /**
     * 文件路径
     */
    public string $filePath;

    /**
     * 存储磁盘
     */
    public string $storageDisk;

    /**
     * 创建一个新的事件实例
     *
     * @return void
     */
    public function __construct(int $fileId, string $filePath, string $storageDisk)
    {
        $this->fileId = $fileId;
        $this->filePath = $filePath;
        $this->storageDisk = $storageDisk;
    }
}
