<?php

namespace Modules\FeatureSms\Rules;

use App\Module\Sms\Services\SmsService;
use DLaravel\Exception\LogicException;
use DLaravel\Validator;

/**
 * 手机验证码验证器
 */
class PhoneCodeValidator extends Validator
{
    /**
     * 验证规则
     */
    public function rules(): array
    {
        return [
            'type' => 'required|string',
            'phone' => 'required|string',
            'code' => 'required|string|size:6',
        ];
    }

    /**
     * 错误消息
     */
    public function messages(): array
    {
        return [
            'type.required' => '验证码类型不能为空',
            'type.string' => '验证码类型必须是字符串',
            'phone.required' => '手机号不能为空',
            'phone.string' => '手机号必须是字符串',
            'code.required' => '验证码不能为空',
            'code.string' => '验证码必须是字符串',
            'code.size' => '验证码必须是6位数字',
        ];
    }

    public function validate(mixed $value, array $data): bool
    {
        return true;

        return self::validateCode($data['type'], $data['phone'], $data['code']);
    }

    /**
     * 验证手机验证码
     *
     * @param  string  $type  验证码类型
     * @param  string  $phone  手机号
     * @param  string  $code  验证码
     *
     * @throws LogicException
     */
    public function validateCode(string $type, string $phone, string $code): bool
    {
        if (empty($type) || empty($phone) || empty($code)) {
            throw new LogicException('参数错误');
        }

        $smsService = app(SmsService::class);

        return $smsService->verifyCode($type, $phone, $code);
    }
}
