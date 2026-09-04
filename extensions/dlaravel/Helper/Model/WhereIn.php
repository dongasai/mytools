<?php

namespace DLaravel\Helper\Model;

/**
 * where in 条件交集封装
 *
 * 用于处理 where in 查询条件的交集操作
 */
class WhereIn
{
    /**
     * 构造函数
     *
     * @param  array  $ids  初始ID数组
     * @param  int  $no  无效ID值
     */
    public function __construct(public array $ids = [], private int $no = -999999) {}

    /**
     * 追加数据
     *
     * @param  array  $ids  要追加的ID数组
     * @return $this
     */
    public function addIds(array $ids): self
    {
        if ($ids) {
            if ($this->ids) {
                $this->ids = array_intersect($ids, $this->ids);
                if (! $this->ids) {
                    $this->ids = [$this->no];
                }
            } else {
                $this->ids = $ids;
            }
        } else {
            $this->ids = [$this->no];
        }

        return $this;
    }

    /**
     * 检查是否有有效数据
     */
    public function notEmpty(): bool
    {
        return ! empty($this->ids);
    }

    /**
     * 获取数据
     */
    public function getData(): array
    {
        return $this->ids;
    }
}
