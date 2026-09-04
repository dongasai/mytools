<?php

namespace DLaravel\Validator;




/**
 * 递归验证，对其数值使用验证器进行验证
 *
 */
class RecursionValidator extends Validator
{

    public function validate(mixed $value, array $data): bool
    {


        return true;
    }

}
