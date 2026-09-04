<?php

namespace DLaravel\Helper;

class File
{
    /**
     * 文件大小
     *
     * @return string
     */
    public static function sizecount($filesize)
    {
        if ($filesize == null || $filesize == '' || $filesize == 0) {
            return '0';
        }
        if ($filesize >= 1073741824) {
            $filesize = round($filesize / 1073741824 * 100) / 100 .' gb';
        } elseif ($filesize >= 1048576) {
            $filesize = round($filesize / 1048576 * 100) / 100 .' mb';
        } elseif ($filesize >= 1024) {
            $filesize = round($filesize / 1024 * 100) / 100 .' kb';
        } else {
            $filesize = $filesize.' bytes';
        }

        return $filesize;
    }

    /**
     * 连接组装
     *
     * @return string
     */
    public static function magnet($_infohash)
    {
        return 'magnet:?xt=urn:btih:'.$_infohash;
    }
}
