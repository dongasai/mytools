<?php

namespace Modules\AFile\Logics;

/**
 * 文件类型配置类
 *
 * 读取 file_types.php 配置，提供允许上传的文件/图片类型和大小限制
 * 修改 Config/file_types.php 即可调整，无需改控制器代码
 */
class FileTypeConfig
{
    /**
     * 配置缓存
     */
    private static ?array $config = null;

    /**
     * 获取配置（带缓存）
     *
     * @return array
     */
    private static function getConfig(): array
    {
        if (self::$config === null) {
            self::$config = require __DIR__.'/../config/file_types.php';
        }

        return self::$config;
    }

    /**
     * 获取允许的文件 MIME 类型字符串（用于验证规则）
     */
    public static function getFileMimes(): string
    {
        return self::getConfig()['file']['mimes'];
    }

    /**
     * 获取文件最大大小（KB）
     */
    public static function getFileMaxSize(): int
    {
        return (int) self::getConfig()['file']['max_size'];
    }

    /**
     * 获取允许的图片 MIME 类型字符串（用于验证规则）
     */
    public static function getImageMimes(): string
    {
        return self::getConfig()['image']['mimes'];
    }

    /**
     * 获取图片最大大小（KB）
     */
    public static function getImageMaxSize(): int
    {
        return (int) self::getConfig()['image']['max_size'];
    }

    /**
     * 构建文件验证规则字符串
     */
    public static function fileRule(): string
    {
        $c = self::getConfig();

        return 'required|file|mimes:'.$c['file']['mimes'].'|max:'.$c['file']['max_size'];
    }

    /**
     * 构建临时文件上传验证规则字符串
     *
     * 在 fileRule() 基础上增加 csv 类型支持
     *
     * @return string 验证规则字符串
     */
    public static function tempFileRule(): string
    {
        $c = self::getConfig();
        $mimes = $c['file']['mimes'].',csv';

        return 'required|file|mimes:'.$mimes.'|max:'.$c['file']['max_size'];
    }

    /**
     * 构建图片验证规则字符串
     */
    public static function imageRule(): string
    {
        $c = self::getConfig();

        return 'required|image|mimes:'.$c['image']['mimes'].'|max:'.$c['image']['max_size'];
    }
}
