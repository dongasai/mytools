<?php

namespace DLaravel\Validator;


class PhoneValidator extends Validator
{

    /**
     * @param mixed $value
     * @param array $data
     * @return bool
     * 验证手机号
     */
    public function validate(mixed $value, array $data): bool
    {
        if (!preg_match('/^1[3-9]\d{9}$/', $value)) {
            return false;
        }

        return true;
    }
}
