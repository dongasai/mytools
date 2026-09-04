<?php

namespace Modules\FeatureSms\Rules;

use App\Module\Sms\Services\SmsService;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * 手机验证码验证规则
 *
 * 重构自 PhoneCodeValidator，实现Laravel 12的ValidationRule接口
 * 验证手机验证码的有效性和正确性
 */
class PhoneCodeValidatorRule implements ValidationRule
{
    /**
     * 验证参数
     */
    protected array $args;

    /**
     * 错误消息
     */
    protected string $message;

    /**
     * SMS服务
     */
    protected SmsService $smsService;

    /**
     * 构造函数
     *
     * @param  mixed  $validation  验证对象
     * @param  array  $args  参数数组
     * @param  string|null  $message  自定义错误消息
     */
    public function __construct($validation, array $args = [], ?string $message = null)
    {
        $this->args = $args;
        $this->message = $message ?? '手机验证码验证失败';
        $this->smsService = new SmsService;
    }

    /**
     * 验证指定的属性
     *
     * @param  string  $attribute  属性名
     * @param  mixed  $value  属性值
     * @param  \Closure  $fail  失败回调
     */
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        $code = $value;

        // 获取手机号码和验证码类型
        $phone = $this->args[0] ?? request()->input('phone');
        $type = $this->args[1] ?? request()->input('type');

        if (! $phone) {
            $fail('手机号码不能为空');

            return;
        }

        if (! $type) {
            $fail('验证码类型不能为空');

            return;
        }

        // 验证手机号码格式
        if (! $this->isValidPhoneNumber($phone)) {
            $fail('手机号码格式不正确');

            return;
        }

        // 验证验证码格式
        if (! $this->isValidCodeFormat($code)) {
            $fail('验证码格式不正确');

            return;
        }

        try {
            // 验证验证码是否正确
            if (! $this->smsService->verifyCode($phone, $code, $type)) {
                $fail('验证码错误或已过期');

                return;
            }

            // 检查验证码是否已被使用
            if ($this->smsService->isCodeUsed($phone, $code, $type)) {
                $fail('验证码已被使用');

                return;
            }

            // 检查验证码发送次数限制
            if ($this->smsService->hasExceededSendLimit($phone, $type)) {
                $fail('验证码发送次数已达上限');

                return;
            }

            // 可选：将验证信息保存到请求中
            $infoFieldKey = $this->args[2] ?? null;
            if ($infoFieldKey) {
                request()->merge([$infoFieldKey => [
                    'phone' => $phone,
                    'code' => $code,
                    'type' => $type,
                    'verified_at' => now()->toDateTimeString(),
                ]]);
            }
        } catch (\Exception $e) {
            $fail('验证手机验证码时发生错误: ' . $e->getMessage());
        }
    }

    /**
     * 验证手机号码格式
     *
     * @param  string  $phone  手机号码
     */
    protected function isValidPhoneNumber(string $phone): bool
    {
        // 中国大陆手机号正则表达式
        $pattern = '/^1[3-9]\d{9}$/';

        return preg_match($pattern, $phone) === 1;
    }

    /**
     * 验证验证码格式
     *
     * @param  string  $code  验证码
     */
    protected function isValidCodeFormat(string $code): bool
    {
        // 验证码必须是6位数字
        return preg_match('/^\d{6}$/', $code) === 1;
    }

    /**
     * 静态验证方法
     *
     * @param  string  $phone  手机号码
     * @param  string  $code  验证码
     * @param  string  $type  验证码类型
     */
    public static function verifyCode(string $phone, string $code, string $type): bool
    {
        try {
            $instance = new static(null);

            return $instance->isValidPhoneNumber($phone) &&
                $instance->isValidCodeFormat($code) &&
                (new SmsService)->verifyCode($phone, $code, $type) &&
                ! (new SmsService)->isCodeUsed($phone, $code, $type);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 获取验证码状态
     *
     * @param  string  $phone  手机号码
     * @param  string  $type  验证码类型
     */
    public static function getCodeStatus(string $phone, string $type): array
    {
        try {
            $smsService = new SmsService;

            return [
                'phone' => $phone,
                'type' => $type,
                'has_valid_code' => $smsService->hasValidCode($phone, $type),
                'code_expired' => $smsService->isCodeExpired($phone, $type),
                'send_count_today' => $smsService->getSendCountToday($phone, $type),
                'max_send_count' => $smsService->getMaxSendCount($type),
                'can_send' => ! $smsService->hasExceededSendLimit($phone, $type),
                'last_sent_at' => $smsService->getLastSentAt($phone, $type),
                'cooldown_remaining' => $smsService->getCooldownRemaining($phone, $type),
            ];
        } catch (\Exception $e) {
            return [
                'phone' => $phone,
                'type' => $type,
                'has_valid_code' => false,
                'code_expired' => true,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * 检查是否可以发送验证码
     *
     * @param  string  $phone  手机号码
     * @param  string  $type  验证码类型
     */
    public static function canSendCode(string $phone, string $type): array
    {
        try {
            $instance = new static(null);
            $smsService = new SmsService;

            $canSend = true;
            $reasons = [];

            // 检查手机号码格式
            if (! $instance->isValidPhoneNumber($phone)) {
                $canSend = false;
                $reasons[] = '手机号码格式不正确';
            }

            // 检查发送限制
            if ($smsService->hasExceededSendLimit($phone, $type)) {
                $canSend = false;
                $reasons[] = '今日发送次数已达上限';
            }

            // 检查冷却时间
            if ($smsService->isInCooldownPeriod($phone, $type)) {
                $canSend = false;
                $reasons[] = '发送间隔未到，请稍后再试';
            }

            return [
                'can_send' => $canSend,
                'reasons' => $reasons,
                'phone_valid' => $instance->isValidPhoneNumber($phone),
                'send_limit_reached' => $smsService->hasExceededSendLimit($phone, $type),
                'in_cooldown' => $smsService->isInCooldownPeriod($phone, $type),
                'cooldown_remaining' => $smsService->getCooldownRemaining($phone, $type),
            ];
        } catch (\Exception $e) {
            return [
                'can_send' => false,
                'reasons' => ['系统错误: ' . $e->getMessage()],
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * 生成验证码
     *
     * @param  int  $length  验证码长度，默认6位
     */
    public static function generateCode(int $length = 6): string
    {
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= random_int(0, 9);
        }

        return $code;
    }

    /**
     * 获取支持验证码类型
     */
    public static function getSupportedTypes(): array
    {
        return [
            'login' => '登录验证',
            'register' => '注册验证',
            'reset_password' => '重置密码',
            'change_phone' => '更换手机号',
            'payment' => '支付验证',
            'operation' => '操作验证',
        ];
    }

    /**
     * 创建静态实例
     *
     * @param  mixed  $validation  验证对象
     * @param  array  $args  参数数组
     * @param  string|null  $message  自定义错误消息
     */
    public static function make($validation, array $args = [], ?string $message = null): static
    {
        return new static($validation, $args, $message);
    }
}
