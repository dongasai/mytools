<?php

namespace Modules\AFile;

use Modules\AFile\Models\FileImg;
use Modules\AFile\Services\ImgService;

/**
 * 图片辅助类（静态方法）
 *
 * 提供便捷的静态方法访问图片服务
 */
class Img
{
    /**
     * 通过ID获取图片URL
     *
     * @param int $id 图片ID
     * @param bool $private 是否私有（私有图片需要特殊处理）
     * @return string 图片URL
     */
    public static function getPicUrl4Id(int $id, bool $private = false): string
    {
        $imgService = new ImgService();
        return $imgService->getPicUrl4Id($id, $private);
    }

    /**
     * 获取管理后台图片URL
     *
     * @param string $path 图片路径
     * @return string 图片URL
     */
    public static function getAdminPicUrl(string $path): string
    {
        $imgService = new ImgService();
        return $imgService->getAdminPicUrl($path);
    }

    /**
     * 获取管理后台图片源地址（不含域名）
     *
     * @param string $path 图片路径
     * @return string 图片源地址
     */
    public static function getAdminPicSrc(string $path): string
    {
        $imgService = new ImgService();
        return $imgService->getAdminPicSrc($path);
    }

    /**
     * 检查后台图片是否存在
     *
     * @param string $path 图片路径
     * @return bool 是否存在
     */
    public static function hasAdminPic(string $path): bool
    {
        $imgService = new ImgService();
        return $imgService->hasAdminPic($path);
    }

    /**
     * 保存后台图片
     *
     * @param string $path 图片路径
     * @param mixed $data 图片数据
     * @return bool 是否成功
     */
    public static function saveAdminPic(string $path, $data): bool
    {
        $imgService = new ImgService();
        return $imgService->saveAdminPic($path, $data);
    }

    /**
     * 通过模型获取图片URL
     *
     * @param FileImg $fileImg 图片模型
     * @param bool $private 是否私有
     * @return string 图片URL
     */
    public static function getPicUrl(FileImg $fileImg, bool $private = false): string
    {
        $imgService = new ImgService();
        return $imgService->getPicUrl($fileImg, $private);
    }
}