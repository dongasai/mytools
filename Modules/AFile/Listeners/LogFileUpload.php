<?php

namespace Modules\AFile\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\AFile\Events\FileUploaded;

/**
 * 记录文件上传日志
 */
class LogFileUpload
{
    /**
     * 处理事件
     *
     * @param  FileUploaded  $event  文件上传事件
     * @return void
     */
    public function handle(FileUploaded $event)
    {
        Log::info('File uploaded', [
            'file_id' => $event->file->id,
            'file_name' => $event->file->o_name,
            'file_path' => $event->file->path,
            'file_size' => $event->file->fsize,
            'user_id' => $event->userId,
        ]);
    }
}
