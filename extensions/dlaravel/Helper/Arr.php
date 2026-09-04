<?php

namespace DLaravel\Helper;

/**
 * 数组判断工具
 */
class Arr
{
    public function __construct(public array $data) {}

    /**
     * 判断数据是否 为null
     *
     * @return bool
     */
    public function isNull($index)
    {
        $va = $this->data[$index] ?? null;

        return is_null($va);
    }

    /**
     * 判断数据是否为 number(数字或数字字符串)
     *
     * @return bool
     */
    public function isNumber($index)
    {
        $va = $this->data[$index] ?? null;

        return is_numeric($va);
    }

    /**
     * 获取int 字符串
     */
    public function getNumber($index): int
    {
        $va = (int) $this->data[$index] ?? 0;

        return (string) $va;
    }

    /**
     * 获取int数据
     */
    public function getInt($index): int
    {
        $va = (int) $this->data[$index] ?? 0;

        return $va;
    }
}
