<?php

namespace DLaravel\Helper;

class Url
{
    public static function getRealUrl($path)
    {
        $host = env('APP_URL', 'http://localhost');

        return $host.''.$path;
    }
}
