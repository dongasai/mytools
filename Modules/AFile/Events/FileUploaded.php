<?php

namespace Modules\AFile\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\AFile\Models\FileFile;

/**
 * 文件上传完成事件
 */
class FileUploaded
{
    use Dispatchable, SerializesModels;

    /**
     * 上传的文件
     *
     * @var FileFile
     */
    public $file;

    /**
     * 用户ID
     *
     * @var int
     */
    public $userId;

    /**
     * 创建事件实例
     *
     * @param  FileFile  $file  上传的文件
     * @param  int  $userId  用户ID
     * @return void
     */
    public function __construct(FileFile $file, int $userId)
    {
        $this->file = $file;
        $this->userId = $userId;
    }
}
