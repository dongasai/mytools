<?php

namespace DLaravel\Entity;

class BaseEntity
{
    /**
     * 使用数组进行初始化
     */
    public static function make4Array($array): static
    {
        $e = new static;
        foreach ($array as $k => $v) {
            $e->$k = $v;
        }

        return $e;
    }
}
