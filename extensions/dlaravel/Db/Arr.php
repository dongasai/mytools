<?php

namespace DLaravel\Db;

/**
 * 数组判断工具
 */
class Arr
{
    public array $data;

    /**
     * @var \Illuminate\Database\Eloquent\Builder|null
     */
    public $query;

    use Query;

    public function __construct($data, $query)
    {
        $this->data = $data;
        $this->query = $query;
    }

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
     * 不是空
     *
     * @return bool
     */
    public function notNull($index)
    {
        $va = $this->data[$index] ?? null;

        return ! is_null($va);
    }

    /**
     * 不为空
     *
     * @return bool
     */
    public function notEmpty($index)
    {
        $va = $this->data[$index] ?? null;

        return ! empty($va);

    }

    /**
     * 大于0
     *
     * @return bool
     */
    public function gt0($index)
    {
        $va = $this->data[$index] ?? null;

        return $va > 0;
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
     * 是否未字符串
     *
     * @return bool
     */
    public function isString($index)
    {
        $va = $this->data[$index] ?? null;

        return is_string($va);
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
