<?php

namespace Modules\AFile\Services;

use Illuminate\Support\Facades\Storage;
use Modules\AFile\Models\FileImg;

/**
 * 图片服务类
 *
 * 提供图片处理相关的静态方法
 */
class ImgService
{
    /**
     * 通过ID获取图片模型
     *
     * @param int $id 图片ID
     * @return FileImg|null 图片模型
     */
    public static function getImageById(int $id): ?FileImg
    {
        return FileImg::find($id);
    }

    /**
     * 获取图片响应流
     *
     * @param FileImg $image 图片模型
     * @return mixed 图片响应流
     */
    public static function getResponse(FileImg $image): mixed
    {
        return Storage::disk($image->storage_disk)->response($image->path);
    }

    /**
     * 获取用户图片列表
     *
     * @param int $userId 用户ID
     * @param array $filters 筛选条件
     * @param int $perPage 每页数量
     * @param int $page 页码
     * @return array 分页数据数组
     */
    public static function getUserImageList(int $userId, array $filters = [], int $perPage = 20, int $page = 1): array
    {
        $query = FileImg::query()->orderBy('created_at', 'desc');

        // 筛选条件
        if (isset($filters['private'])) {
            $query->where('private', $filters['private']);
        }
        if (isset($filters['re_type'])) {
            $query->where('re_type', $filters['re_type']);
        }
        if (isset($filters['re_id'])) {
            $query->where('re_id', $filters['re_id']);
        }

        // 仅返回用户的图片（私有图片）或公共图片
        $query->where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhere('private', 0);
        });

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $items = collect($paginator->items())->map(function ($image) {
            return [
                'id' => $image->id,
                'original_name' => $image->o_name,
                'file_size' => $image->fsize,
                'width' => $image->width,
                'height' => $image->height,
                'file_type' => $image->type1,
                'is_private' => $image->private,
                're_type' => $image->re_type,
                're_id' => $image->re_id,
                'url' => self::getPicUrl($image, $image->private === 1),
                'created_at' => $image->created_at->toIso8601String(),
            ];
        });

        return [
            'items' => $items,
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
        ];
    }

    /**
     * 获取图片模型
     *
     * @param  string  $path  图片路径
     * @return FileImg|null 图片模型
     */
    public static function getModel(string $path): ?FileImg
    {
        $model = FileImg::where('path', $path)->first();

        return $model;
    }

    /**
     * 下载图片
     *
     * @param  FileImg  $fileImg  图片模型
     * @return mixed 下载响应
     */
    public static function download(FileImg $fileImg): mixed
    {
        return Storage::disk($fileImg->storage_disk)->download($fileImg->path);
    }

    /**
     * 通过ID获取图片URL
     *
     * @param  int  $id  图片ID
     * @return string 图片URL
     */
    public static function getPicUrl4Id(int $id): string
    {
        $model = FileImg::find($id);
        if ($model) {
            return self::getPicUrl($model);
        }

        return '';
    }

    /**
     * 获取图片的可用访问地址
     *
     * @param  FileImg  $fileImg  图片模型
     * @param  bool  $private  是否私有（已废弃参数，自动从模型读取）
     * @return string 图片URL（完整URL，包含域名）
     */
    public static function getPicUrl(FileImg $fileImg, bool $private = false): string
    {
        // 私有图片返回API访问地址
        if ($fileImg->private == 1) {
            return url("/api/file/image/{$fileImg->id}/download/private");
        }

        // 公开图片返回完整URL
        $storageUrl = Storage::disk($fileImg->storage_disk)->url($fileImg->path);

        // 如果返回的是相对路径，拼接完整URL
        if (str_starts_with($storageUrl, '/')) {
            return rtrim(config('app.url'), '/') . $storageUrl;
        }

        return $storageUrl;
    }

    /**
     * 获取后台上传的图片的访问地址
     *
     * @param  string  $path  图片路径
     * @return string 图片URL
     */
    public static function getAdminPicUrl(string $path): string
    {
        $disk = config('admin.upload.disk');
        $src = '';

        if (config("filesystems.disks.{$disk}")) {
            $src = Storage::disk($disk)->url($path);
        }

        return $src;
    }

    /**
     * 获取后台上传的图片的源地址
     *
     * @param  string  $path  图片路径
     * @return string 图片源地址
     */
    public static function getAdminPicSrc(string $path): string
    {
        $disk = config('admin.upload.disk');
        $src = '';

        if (config("filesystems.disks.{$disk}")) {
            $src = Storage::disk($disk)->url($path);
        }
        $ss = parse_url($src);

        return $ss['path'] ?? '';
    }

    /**
     * 保存后台图片
     *
     * @param  string  $path  图片路径
     * @param  mixed  $data  图片数据
     * @return bool 是否成功
     */
    public static function saveAdminPic(string $path, mixed $data): bool
    {
        $disk = config('admin.upload.disk');

        if (config("filesystems.disks.{$disk}")) {
            return Storage::disk($disk)->put($path, $data);
        }

        return false;
    }

    /**
     * 检查后台图片是否存在
     *
     * @param  string  $path  图片路径
     * @return bool 是否存在
     */
    public static function hasAdminPic(string $path): bool
    {
        $disk = config('admin.upload.disk');

        if (config("filesystems.disks.{$disk}")) {
            return Storage::disk($disk)->exists($path);
        }

        return false;
    }

    /**
     * 图片数组处理为可访问的地址数组
     *
     * @param  array  $arr  图片数组
     * @return array 图片URL数组
     */
    public static function imgArr2imgArrurl(array $arr): array
    {
        $res = [];
        foreach ($arr as $img) {
            $res[] = self::img2imgurl($img);
        }

        return $res;
    }

    /**
     * 图片路径处理为可访问的地址
     *
     * @param  string  $img  图片路径
     * @return string 图片URL
     */
    public static function img2imgurl(string $img): string
    {
        if (substr($img, 0, 8) == '/storage') {
            return self::getAdminPicUrl(substr($img, 8));
        }

        return $img;
    }
}
