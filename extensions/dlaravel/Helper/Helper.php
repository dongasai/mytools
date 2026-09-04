<?php

namespace DLaravel\Helper;

/**
 * 通用助手类
 */
class Helper
{
    /**
     * 检查值是否不为空
     *
     * @param  mixed  $value  要检查的值
     * @return bool
     */
    public static function not_null($value)
    {
        return ! is_null($value);
    }
}
