<?php

namespace Modules\AFile;

use Modules\AFile\Services\TemporaryService;

/**
 * 临时文件辅助类（静态方法）
 *
 * 提供便捷的静态方法访问临时文件服务
 */
class Temporary
{
    /**
     * 保存临时文件
     *
     * @param string $ext 文件扩展名
     * @param string $content 文件内容
     * @return string 临时文件路径
     */
    public static function save(string $ext, string $content): string
    {
        $temporaryService = new TemporaryService();
        return $temporaryService->save($ext, $content);
    }

    /**
     * 获取临时文件下载URL
     *
     * @param string $path 临时文件路径
     * @return string 下载URL
     */
    public static function getDownUrl(string $path): string
    {
        $temporaryService = new TemporaryService();
        return $temporaryService->getDownUrl($path);
    }

    /**
     * 删除临时文件
     *
     * @param string $path 临时文件路径
     * @return bool 是否成功
     */
    public static function delete(string $path): bool
    {
        $temporaryService = new TemporaryService();
        return $temporaryService->delete($path);
    }

    /**
     * 检查临时文件是否存在
     *
     * @param string $path 临时文件路径
     * @return bool 是否存在
     */
    public static function exists(string $path): bool
    {
        $temporaryService = new TemporaryService();
        return $temporaryService->exists($path);
    }

    /**
     * 获取临时文件本地绝对路径
     *
     * @param string $path 相对路径
     * @return string 本地绝对路径
     */
    public static function getLocalPath(string $path): string
    {
        $temporaryService = new TemporaryService();
        return $temporaryService->getLocalPath($path);
    }
}