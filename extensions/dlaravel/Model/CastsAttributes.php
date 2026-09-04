<?php

namespace DLaravel\Model;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;

abstract class CastsAttributes implements \Illuminate\Contracts\Database\Eloquent\CastsAttributes, Arrayable
{
    public function toArray()
    {
        $data = [];
        foreach (get_object_vars($this) as $key => $value) {
            if ($key === 'model' || $key === 'key' || $key === 'value' || $key === 'attributes') {
                continue;
            }
            if ($value instanceof Arrayable) {
                $data[$key] = $value->toArray();
            } else {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    /**
     * 将取出的数据转换为对应的类型
     *
     * @param  Model  $model
     * @param  mixed  $value
     * @return array
     */
    public function get($model, string $key, $value, array $attributes): static
    {

        $ob = json_decode($value, true);
        if (empty($ob)) {
            $ob = json_decode('{}', true);
        }
        $res = new static;
        $res->setData($ob);

        return $res;
    }

    public function setData(array $data)
    {
        foreach ($data as $key => $value) {
            if (is_null($value)) {
                continue;
            }
            $this->{$key} = $value;
        }

        return $this;

    }

    /**
     * 将给定的值转换为存储格式
     *
     * @param  Model  $model
     * @param  mixed  $value
     * @return string|null
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if (is_null($value)) {
            return json_encode([], JSON_UNESCAPED_UNICODE);
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 判断字符串是否为有效的JSON
     *
     * @param  string  $value
     * @return bool
     */
    protected function isJson($value)
    {
        if (! is_string($value)) {
            return false;
        }

        json_decode($value);

        return json_last_error() === JSON_ERROR_NONE;
    }
}
