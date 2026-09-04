<?php

namespace DLaravel\Helper;

/**
 * 应对json的对象默认值是[]
 */
class JsonOb implements \JsonSerializable
{
    public function __construct(private $data = []) {}

    /**
     * 默认对象
     *
     * @return JsonOb|array|object
     */
    public static function deObject($oba)
    {
        if (is_object($oba)) {
            return $oba;
        } elseif (is_array($oba)) {
            if ($oba === []) {
                return new JsonOb;
            }

            return new JsonOb($oba);

        } else {
            return new JsonOb;
        }
    }

    public function isEmpty()
    {
        return empty($this->data);
    }

    public function __toString(): string
    {
        if ($this->data) {
            return json_encode($this->data);

        }

        return '{}';
    }

    public function jsonSerialize()
    {
        if ($this->data) {
            return $this->data;

        }

        return new \stdClass;
    }
}
