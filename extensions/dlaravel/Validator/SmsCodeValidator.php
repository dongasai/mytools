<?php

namespace DLaravel\Validator;


class SmsCodeValidator extends Validator
{

    /**
     * @param mixed $value
     * @param array $data
     * @return bool
     * 短信验证码 6位纯数字
     */
    public function validate(mixed $value, array $data): bool
    {
        if (!preg_match('/^\d{6}$/', $value)) {
            return false;
        }

        return true;
    }
}
