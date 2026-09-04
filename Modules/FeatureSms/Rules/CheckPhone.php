<?php

namespace Modules\FeatureSms\Rules;

use App\Module\User\Validator\IsNotRegister;
use DLaravel\ValidationCore;
use DLaravel\Validator\PhoneValidator;

/**
 * 注册发送短信验证
 */
class CheckPhone extends ValidationCore
{
    public function rules($rules = []): array
    {
        $rules = [
            [
                'mobile',
                'required',
            ],
            [
                'mobile',
                new PhoneValidator($this),
                'msg' => '不存在的手机号码',
            ],
            [
                'mobile',
                new IsNotRegister($this),
                'msg' => '该手机号已注册',
            ],
        ];

        return parent::rules($rules);
    }
}
