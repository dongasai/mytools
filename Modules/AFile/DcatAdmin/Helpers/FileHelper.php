<?php

namespace Modules\AFile\DcatAdmin\Helpers;

/**
 * 文件辅助工具类
 *
 * 提供文件模块后台的通用功能
 */
class FileHelper
{
    /**
     * 格式化文件大小
     *
     * @param  int  $size  文件大小（字节）
     * @return string 格式化后的文件大小
     */
    public static function formatFileSize(int $size): string
    {
        if ($size >= 1073741824) {
            return round($size / 1073741824, 2) . ' GB';
        } elseif ($size >= 1048576) {
            return round($size / 1048576, 2) . ' MB';
        } elseif ($size >= 1024) {
            return round($size / 1024, 2) . ' KB';
        } else {
            return $size . ' B';
        }
    }

    /**
     * 获取文件类型图标
     *
     * @param  string  $type  文件类型
     * @return string 图标HTML
     */
    public static function getFileTypeIcon(string $type): string
    {
        $type = strtolower($type);

        $icons = [
            'pdf' => '<i class="fa fa-file-pdf-o"></i>',
            'doc' => '<i class="fa fa-file-word-o"></i>',
            'docx' => '<i class="fa fa-file-word-o"></i>',
            'xls' => '<i class="fa fa-file-excel-o"></i>',
            'xlsx' => '<i class="fa fa-file-excel-o"></i>',
            'ppt' => '<i class="fa fa-file-powerpoint-o"></i>',
            'pptx' => '<i class="fa fa-file-powerpoint-o"></i>',
            'jpg' => '<i class="fa fa-file-image-o"></i>',
            'jpeg' => '<i class="fa fa-file-image-o"></i>',
            'png' => '<i class="fa fa-file-image-o"></i>',
            'gif' => '<i class="fa fa-file-image-o"></i>',
            'webp' => '<i class="fa fa-file-image-o"></i>',
            'zip' => '<i class="fa fa-file-archive-o"></i>',
            'rar' => '<i class="fa fa-file-archive-o"></i>',
            'txt' => '<i class="fa fa-file-text-o"></i>',
        ];

        return $icons[$type] ?? '<i class="fa fa-file-o"></i>';
    }

    /**
     * 检查文件是否为图片
     *
     * @param  string  $type  文件类型
     * @return bool 是否为图片
     */
    public static function isImage(string $type): bool
    {
        $type = strtolower($type);
        $imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];

        return in_array($type, $imageTypes);
    }
}
