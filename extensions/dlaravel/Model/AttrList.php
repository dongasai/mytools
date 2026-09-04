<?php

namespace DLaravel\Model;

trait AttrList
{
    // attrlist start
    public static $attrlist = [];
    // attrlist end

    /**
     * 设置数据
     *
     * @param  array  $data
     * @param  bool  $onlyAttr
     * @return $this
     */
    public function setData($data, $onlyAttr = false)
    {
        foreach ($data as $k => $datum) {
            if (! $onlyAttr || in_array($k, static::$attrlist)) {
                $this->$k = $datum;
            }
        }

        return $this;
    }
}
