<?php

namespace Modules\FeatureSms\Rules;

use Illuminate\Contracts\Validation\ValidationRule;

/**
 * 未注册验证规则
 *
 * 验证手机号码是否未注册
 */
class IsNotRegisterRule implements ValidationRule
{
    /**
     * 验证规则
     *
     * @param  string  $attribute  属性名
     * @param  mixed  $value  属性值
     * @param  \Closure  $fail  回调函数
     */
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        if (! $this->isNotRegistered($value)) {
            $fail('该手机号已注册');
        }
    }

    /**
     * 检查手机号码是否未注册
     *
     * @param  mixed  $value
     */
    public function isNotRegistered($value): bool
    {
        if (empty($value)) {
            return true; // 空值视为未注册
        }

        // 格式化手机号码
        $phone = $this->formatPhoneNumber($value);

        // 这里应该查询数据库检查手机号是否已注册
        // 暂时返回true，表示未注册
        return $this->checkPhoneNotRegistered($phone);
    }

    /**
     * 格式化手机号码
     *
     * @param  mixed  $value
     */
    protected function formatPhoneNumber($value): string
    {
        // 移除所有非数字字符
        return preg_replace('/\D/', '', (string) $value);
    }

    /**
     * 检查手机号是否未注册（数据库查询）
     *
     * @param  string  $phone
     */
    protected function checkPhoneNotRegistered($phone): bool
    {
        // 这里应该实现数据库查询逻辑
        // 例如：SELECT COUNT(*) FROM users WHERE phone = :phone

        // 模拟数据库查询结果
        // 实际应该查询用户表或手机号码表

        // 假设手机号段用于测试
        $registeredPrefixes = [
            '13800000001',
            '13800000002',
            '13800000003', // 示例已注册手机号
            '13900000001',
            '13900000002',
            '13900000003',
        ];

        // 如果手机号在已注册列表中，返回false（已注册）
        if (in_array($phone, $registeredPrefixes)) {
            return false;
        }

        // 否则返回true（未注册）
        return true;
    }

    /**
     * 检查手机号注册状态
     */
    public function getRegistrationStatus(string $phone): array
    {
        $status = [
            'phone' => $phone,
            'is_registered' => false,
            'registration_time' => null,
            'user_info' => null,
        ];

        // 这里应该查询数据库获取详细信息
        // 暂时返回默认状态

        return $status;
    }

    /**
     * 验证多个手机号是否都未注册
     */
    public function validateMultiplePhones(array $phones): array
    {
        $results = [];

        foreach ($phones as $phone) {
            $results[$phone] = [
                'phone' => $phone,
                'is_not_registered' => $this->isNotRegistered($phone),
                'can_register' => $this->isNotRegistered($phone),
            ];
        }

        return $results;
    }

    /**
     * 静态创建方法
     */
    public static function make(): self
    {
        return new self;
    }

    /**
     * 获取验证规则参数
     */
    public function getParameters(): array
    {
        return [
            'rule_type' => 'not_register_validation',
            'description' => '验证手机号码是否未注册',
            'supports_multiple' => true,
        ];
    }

    /**
     * 设置错误消息
     */
    public function setErrorMessage(string $message): self
    {
        // 这个方法用于自定义错误消息
        // 在当前的Laravel ValidationRule接口中，错误消息在validate方法中通过$fail回调传递
        // 这里保留接口以便将来扩展

        return $this;
    }

    /**
     * 获取默认错误消息
     */
    public function getDefaultErrorMessage(): string
    {
        return '该手机号已注册';
    }

    /**
     * 检查是否可以发送验证码
     */
    public function canSendVerificationCode(string $phone): bool
    {
        // 首先检查是否未注册
        if (! $this->isNotRegistered($phone)) {
            return false;
        }

        // 这里可以添加其他验证逻辑
        // 例如：检查发送频率限制、IP限制等

        return $this->checkSendingLimits($phone);
    }

    /**
     * 检查发送限制
     */
    protected function checkSendingLimits(string $phone): bool
    {
        // 这里应该实现发送频率限制检查
        // 例如：检查该手机号1分钟内是否已发送过验证码
        // 或者检查IP地址的发送频率限制

        // 模拟限制检查
        return true; // 暂时返回true，表示可以发送
    }

    /**
     * 获取发送限制信息
     */
    public function getSendingLimits(string $phone): array
    {
        return [
            'phone' => $phone,
            'can_send' => $this->canSendVerificationCode($phone),
            'last_sent_time' => null, // 这里应该记录上次发送时间
            'remaining_count' => 5,    // 剩余可发送次数
            'time_until_next_send' => 0, // 距离下次可发送的秒数
        ];
    }
}
