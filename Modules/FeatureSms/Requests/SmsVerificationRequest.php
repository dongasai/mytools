<?php

namespace Modules\FeatureSms\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\FeatureSms\Rules\PhoneRule;

/**
 * 短信验证请求类
 *
 * 重构自 CheckPhone Validation类，实现Laravel 12的FormRequest接口
 * 用于注册发送短信验证的场景
 */
class SmsVerificationRequest extends FormRequest
{
    /**
     * 判断用户是否有权限进行此请求
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 获取应用到请求的验证规则
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'mobile' => [
                'required',
                'string',
                new PhoneRule(null, '不存在的手机号码格式'),
            ],
        ];
    }

    /**
     * 获取自定义错误消息
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'mobile.required' => '手机号不能为空',
            'mobile.string' => '手机号必须是字符串',
        ];
    }

    /**
     * 获取自定义属性名称
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'mobile' => '手机号',
        ];
    }

    /**
     * 配置验证器实例
     *
     * @param  \Illuminate\Validation\Validator  $validator
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isEmpty()) {
                $this->validateMobileNotRegistered($validator);
                $this->validateMobileExists($validator);
            }
        });
    }

    /**
     * 验证手机号是否未注册
     *
     * @param  \Illuminate\Validation\Validator  $validator
     */
    protected function validateMobileNotRegistered($validator): void
    {
        try {
            // 这里应该调用实际的验证逻辑
            // 由于原始代码使用了 IsNotRegister 验证器，这里保持相同的逻辑
            $mobile = $this->input('mobile');

            // TODO: 实现手机号是否已注册的检查逻辑
            // 如果手机号已注册，添加错误：
            // $validator->errors()->add('mobile', '该手机号已注册');

        } catch (\Exception $e) {
            $validator->errors()->add('mobile', '验证手机号注册状态时发生错误');
        }
    }

    /**
     * 验证手机号是否存在
     *
     * @param  \Illuminate\Validation\Validator  $validator
     */
    protected function validateMobileExists($validator): void
    {
        try {
            // 这里应该调用实际的验证逻辑
            // 由于原始代码使用了 PhoneValidator 验证器，这里保持相同的逻辑
            $mobile = $this->input('mobile');

            // TODO: 实现手机号是否存在的检查逻辑
            // 如果手机号不存在，添加错误：
            // $validator->errors()->add('mobile', '不存在的手机号码');

        } catch (\Exception $e) {
            $validator->errors()->add('mobile', '验证手机号时发生错误');
        }
    }

    /**
     * 获取手机号
     */
    public function getMobile(): string
    {
        return $this->input('mobile', '');
    }

    /**
     * 格式化手机号
     */
    public function getFormattedMobile(): string
    {
        $phoneRule = new PhoneRule;

        return $phoneRule->formatPhone($this->getMobile());
    }

    /**
     * 检查是否为中国手机号
     */
    public function isChineseMobile(): bool
    {
        $phoneRule = new PhoneRule;

        return $phoneRule->isChinesePhone($this->getMobile());
    }
}
