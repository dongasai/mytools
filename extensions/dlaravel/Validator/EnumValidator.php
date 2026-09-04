<?php

namespace DLaravel\Validator;


/**
 * 枚举in验证
 * int,name可用
 *
 */
class EnumValidator extends Validator
{


    public function validate(mixed $value, array $data): bool
    {
        $enmuClass = $this->args[0];
        $ks = $enmuClass::toArray();
//        dd($ks,$value);
        if (in_array($value, array_values($ks))) {
            return true;
        }
        return false;
    }
}
