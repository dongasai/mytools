<?php

namespace Modules\FeatureSms\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\FeatureSms\Rules\IsNotRegisterRule;
use Modules\FeatureSms\Rules\PhoneRule;

/**
 * 短信验证码检查请求验证
 *
 * 用于验证短信验证码发送前的手机号码检查
 */
class CheckPhoneRequest extends FormRequest
{
    /**
     * 确定用户是否有权进行此请求
     */
    public function authorize(): bool
    {
        // 短信验证码检查不需要特殊权限
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
                new PhoneRule,
                new IsNotRegisterRule,
            ],
        ];
    }

    /**
     * 获取自定义错误消息
     */
    public function messages(): array
    {
        return [
            'mobile.required' => '手机号码不能为空',
            'mobile.string' => '手机号码必须是字符串',
            'mobile.phone' => '手机号码格式不正确',
            'mobile.not_register' => '该手机号已注册',
        ];
    }

    /**
     * 获取自定义属性名称
     */
    public function attributes(): array
    {
        return [
            'mobile' => '手机号码',
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
            // 可以在这里添加额外的验证逻辑
            $this->performAdditionalValidation($validator);
        });
    }

    /**
     * 执行额外的验证逻辑
     *
     * @param  \Illuminate\Validation\Validator  $validator
     */
    protected function performAdditionalValidation($validator): void
    {
        // 检查手机号码是否可以发送验证码
        $mobile = $this->input('mobile');

        if ($mobile && ! $this->canSendSms($mobile)) {
            $validator->errors()->add('mobile', '该手机号暂时无法发送验证码，请稍后再试');
        }
    }

    /**
     * 检查是否可以发送短信验证码
     */
    protected function canSendSms(string $mobile): bool
    {
        // 这里可以添加发送频率限制逻辑
        // 例如：检查该手机号在1分钟内是否已发送过验证码
        // 或者检查IP发送频率限制

        return true; // 暂时返回true，实际应该实现频率检查
    }

    /**
     * 获取手机号码
     */
    public function getMobile(): string
    {
        return $this->input('mobile', '');
    }

    /**
     * 获取格式化后的手机号码
     */
    public function getFormattedMobile(): string
    {
        $mobile = $this->getMobile();

        // 移除所有非数字字符
        $formatted = preg_replace('/\D/', '', $mobile);

        return $formatted;
    }

    /**
     * 检查手机号码是否为中国大陆手机号
     */
    public function isChinaMobile(): bool
    {
        $mobile = $this->getFormattedMobile();

        // 中国大陆手机号正则：1开头，第二位是3-9，总共11位数字
        return preg_match('/^1[3-9]\d{9}$/', $mobile);
    }

    /**
     * 获取手机号码运营商类型
     */
    public function getMobileOperator(): string
    {
        $mobile = $this->getFormattedMobile();

        if (! $this->isChinaMobile()) {
            return 'unknown';
        }

        $prefix = substr($mobile, 0, 3);

        // 根据号段判断运营商
        $mobileOperators = [
            // 中国移动
            '134',
            '135',
            '136',
            '137',
            '138',
            '139',
            '147',
            '148',
            '150',
            '151',
            '152',
            '157',
            '158',
            '159',
            '172',
            '178',
            '182',
            '183',
            '184',
            '187',
            '188',
            '198',
            // 中国联通
            '130',
            '131',
            '132',
            '145',
            '155',
            '156',
            '166',
            '175',
            '176',
            '185',
            '186',
            // 中国电信
            '133',
            '149',
            '153',
            '173',
            '177',
            '180',
            '181',
            '189',
            '191',
            '199',
            // 虚拟运营商
            '170',
            '171',
        ];

        if (in_array($prefix, array_slice($mobileOperators, 0, 22))) {
            return 'china_mobile';
        } elseif (in_array($prefix, array_slice($mobileOperators, 22, 33))) {
            return 'china_unicom';
        } elseif (in_array($prefix, array_slice($mobileOperators, 33, 46))) {
            return 'china_telecom';
        } elseif (in_array($prefix, array_slice($mobileOperators, 46, 48))) {
            return 'virtual_operator';
        }

        return 'unknown';
    }

    /**
     * 验证并返回综合结果
     */
    public function validateAndReturn(): array
    {
        $validator = $this->getValidator();

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()->toArray(),
                'mobile' => $this->getMobile(),
                'formatted_mobile' => $this->getFormattedMobile(),
            ];
        }

        return [
            'success' => true,
            'mobile' => $this->getMobile(),
            'formatted_mobile' => $this->getFormattedMobile(),
            'is_china_mobile' => $this->isChinaMobile(),
            'operator' => $this->getMobileOperator(),
            'can_send_sms' => $this->canSendSms($this->getMobile()),
        ];
    }

    /**
     * 静态创建方法
     */
    public static function make(): self
    {
        return new self;
    }

    /**
     * 验证手机号码并发送验证码
     */
    public function validateAndSendSms(): array
    {
        $result = $this->validateAndReturn();

        if (! $result['success']) {
            return $result;
        }

        // 这里可以添加发送短信验证码的逻辑
        // 例如：调用短信服务API发送验证码

        return array_merge($result, [
            'sms_sent' => true,
            'sms_message' => '验证码已发送，请查收短信',
        ]);
    }
}
