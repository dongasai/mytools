<?php

namespace Modules\AFile\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Modules\AFile\Logics\StorageConfig;
use Modules\AFile\Logics\DirLogic;
use Modules\AFile\Models\FileImg;
use Modules\AFile\Services\StorageConfigService;
use Modules\Application\Services\SystemLogService;

/**
 * 上传服务类
 *
 * 提供文件上传相关的服务
 * 所有方法均为静态方法，参数显性传入，符合无状态设计原则
 */
class UploadService
{
    /**
     * 上传图片
     *
     * @param  UploadedFile  $file  上传的文件
     * @param  int  $userId  用户ID（显性传入）
     * @param  bool  $private  是否私有，默认false
     * @return FileImg 图片模型
     *
     * @throws \Exception 上传失败时抛出异常
     */
    public static function uploadImg(UploadedFile $file, int $userId, bool $private = false): FileImg
    {
        try {
            // 获取用户目录（静态调用）
            $dir = DirLogic::getDir($userId);

            // 重绘图片
            $upfilepath = $file->getRealPath();
            // create image manager with desired driver
            $manager = new ImageManager(new Driver);
            // read image from file system
            $image = $manager->read($upfilepath);

            // resize image proportionally
            $image->scale($image->width(), $image->height());

            // 获取图片尺寸（保存前）
            $width = $image->width();
            $height = $image->height();

            $path = $dir.'/'.uniqid().'.webp';

            // save modified image in new format
            $webpimg = $image->toWebp();
            $fileString = $webpimg->toString();
            $size = $webpimg->size();

            // 从数据库获取默认存储配置
            $defaultDiskConfig = StorageConfigService::getDefaultDisk();
            if (!$defaultDiskConfig) {
                throw new \Exception('未找到默认存储配置');
            }

            $diskName = $defaultDiskConfig->name;

            // 动态注册磁盘配置（确保配置已加载，包括 url 字段）
            $config = array_merge(
                ['driver' => $defaultDiskConfig->driver],
                $defaultDiskConfig->config,
                [
                    'throw' => true,
                    'timeout' => 60,
                    'connect_timeout' => 10,
                ]
            );
            config(["filesystems.disks.{$diskName}" => $config]);

            // 保存到存储
            $res = Storage::disk($diskName)->put($path, $fileString);
            if ($res === false) {
                throw new \Exception('服务器错误,请联系客服.');
            }

            // 创建图片记录
            $f = new FileImg;
            $f->storage_disk = $diskName;
            $f->path = $path;
            $f->user_id = $userId;
            $f->o_name = $file->getClientOriginalName();
            $f->type1 = 'webp';
            $f->fsize = $size;
            $f->width = $width;
            $f->height = $height;
            $f->private = (int) $private;
            $f->re_id = 0;
            $f->re_type = '';
            $f->save();
            $f->refresh();

            return $f;
        } catch (\Throwable $e) {
            SystemLogService::exception('afile', $e, [
                'operation' => 'uploadImg',
                'filename' => $file->getClientOriginalName(),
                'user_id' => $userId,
            ]);
            throw $e;
        }
    }
}