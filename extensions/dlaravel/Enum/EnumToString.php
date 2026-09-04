<?php

namespace DLaravel\Enum;

trait EnumToString
{
    public function valueString(): string
    {
        return $this->value;
    }

    public function value(): string
    {
        return $this->value;
    }
}
