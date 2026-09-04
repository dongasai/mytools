<?php

namespace DLaravel\Enum;

/**
 * 普通枚举
 */
trait EnumName
{
    public function name(): string
    {
        return $this->name;
    }

    public function value(): string
    {
        return $this->name;
    }
}
