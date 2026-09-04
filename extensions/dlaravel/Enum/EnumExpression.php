<?php

namespace DLaravel\Enum;

use Illuminate\Database\Grammar;

/**
 * 数据库转换
 */
trait EnumExpression
{
    public function getValue(Grammar $grammar)
    {
        $res = $this->value();
        if (is_numeric($res)) {
            return $res;
        }

        return "'$res'";
    }
}
