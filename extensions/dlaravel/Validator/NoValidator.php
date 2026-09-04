<?php

namespace DLaravel\Validator;



/**
 * 验证不通过
 *
 */
class NoValidator extends Validator
{
    public function validate(mixed $value, array $data): bool
    {
        $this->addError('暂不可用!');
        return false;
    }

}
