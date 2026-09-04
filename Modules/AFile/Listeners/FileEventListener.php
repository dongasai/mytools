<?php

namespace Modules\AFile\Listeners;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\AFile\Events\FileDeletedEvent;
use Modules\AFile\Events\FileUploadedEvent;
use Modules\AFile\Events\ImageDeletedEvent;
use Modules\AFile\Events\ImageUploadedEvent;

/**
 * 文件事件监听器
 */
class FileEventListener
{
    /**
     * 处理文件上传事件
     */
    public function handleFileUploaded(FileUploadedEvent $event): void
    {
        try {
            // 记录日志
            Log::info('文件上传', [
                'file_id' => $event->file->id,
                'user_id' => $event->file->user_id,
                'path' => $event->file->path,
                'original_name' => $event->file->o_name,
                'size' => $event->file->fsize,
            ]);

            // 这里可以添加其他处理逻辑，如发送通知等
        } catch (\Exception $e) {
            Log::error('处理文件上传事件失败', [
                'error' => $e->getMessage(),
                'file_id' => $event->file->id,
            ]);
        }
    }

    /**
     * 处理图片上传事件
     */
    public function handleImageUploaded(ImageUploadedEvent $event): void
    {
        try {
            // 记录日志
            Log::info('图片上传', [
                'image_id' => $event->image->id,
                'user_id' => $event->image->user_id,
                'path' => $event->image->path,
                'original_name' => $event->image->o_name,
                'size' => $event->image->fsize,
                'private' => $event->image->private,
            ]);

            // 这里可以添加其他处理逻辑，如图片处理等
        } catch (\Exception $e) {
            Log::error('处理图片上传事件失败', [
                'error' => $e->getMessage(),
                'image_id' => $event->image->id,
            ]);
        }
    }

    /**
     * 处理文件删除事件
     */
    public function handleFileDeleted(FileDeletedEvent $event): void
    {
        try {
            // 记录日志
            Log::info('文件删除', [
                'file_id' => $event->fileId,
                'path' => $event->filePath,
                'storage_disk' => $event->storageDisk,
            ]);

            // 尝试从存储中删除文件
            try {
                Storage::disk($event->storageDisk)->delete($event->filePath);
            } catch (\Exception $e) {
                Log::warning('删除存储中的文件失败', [
                    'error' => $e->getMessage(),
                    'file_id' => $event->fileId,
                    'path' => $event->filePath,
                ]);
            }

            // 这里可以添加其他处理逻辑，如清理相关数据等
        } catch (\Exception $e) {
            Log::error('处理文件删除事件失败', [
                'error' => $e->getMessage(),
                'file_id' => $event->fileId,
            ]);
        }
    }

    /**
     * 处理图片删除事件
     */
    public function handleImageDeleted(ImageDeletedEvent $event): void
    {
        try {
            // 记录日志
            Log::info('图片删除', [
                'image_id' => $event->imageId,
                'path' => $event->imagePath,
                'storage_disk' => $event->storageDisk,
            ]);

            // 尝试从存储中删除图片
            try {
                Storage::disk($event->storageDisk)->delete($event->imagePath);
            } catch (\Exception $e) {
                Log::warning('删除存储中的图片失败', [
                    'error' => $e->getMessage(),
                    'image_id' => $event->imageId,
                    'path' => $event->imagePath,
                ]);
            }

            // 这里可以添加其他处理逻辑，如清理相关数据等
        } catch (\Exception $e) {
            Log::error('处理图片删除事件失败', [
                'error' => $e->getMessage(),
                'image_id' => $event->imageId,
            ]);
        }
    }

    /**
     * 注册监听器
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events): void
    {
        $events->listen(
            FileUploadedEvent::class,
            [FileEventListener::class, 'handleFileUploaded']
        );

        $events->listen(
            ImageUploadedEvent::class,
            [FileEventListener::class, 'handleImageUploaded']
        );

        $events->listen(
            FileDeletedEvent::class,
            [FileEventListener::class, 'handleFileDeleted']
        );

        $events->listen(
            ImageDeletedEvent::class,
            [FileEventListener::class, 'handleImageDeleted']
        );
    }
}
