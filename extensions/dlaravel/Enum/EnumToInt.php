<?php

namespace DLaravel\Enum;

trait EnumToInt
{
    public function valueInt(): int
    {
        return $this->value;
    }

    public function value(): int
    {
        return $this->value;
    }
}
