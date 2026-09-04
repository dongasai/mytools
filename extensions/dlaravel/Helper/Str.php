<?php

namespace DLaravel\Helper;

class Str
{
    /**
     * 字符串数组替换
     *
     * @return array|mixed|string|string[]
     */
    public static function strtr($templete, $data)
    {
        $temp = $templete;
        foreach ($data as $k => $v) {
            $temp = str_replace('{'.$k.'}', $v, $temp);
        }

        return $temp;

    }
}
