<?php

namespace Modules\FeatureSms\Rules;

use App\Module\Sms\Services\SmsService;
use Closure;
use DLaravel\Exception\LogicException;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * 手机验证码验证规则
 *
 * 重构自 PhoneCodeValidator，实现Laravel 12的ValidationRule接口
 * 验证手机验证码的正确性
 */
class PhoneCodeRule implements ValidationRule
{
    /**
     * 验证码类型要求
     */
    private ?string $requiredType;

    /**
     * 手机号要求
     */
    private ?string $requiredPhone;

    /**
     * 错误消息
     */
    private string $errorMessage;

    /**
     * 是否启用严格模式（严格匹配手机号和类型）
     */
    private bool $strictMode;

    /**
     * 构造函数
     *
     * @param  string|null  $requiredType  要求的验证码类型
     * @param  string|null  $requiredPhone  要求的手机号
     * @param  string|null  $errorMessage  自定义错误消息
     * @param  bool  $strictMode  是否启用严格模式
     */
    public function __construct(
        ?string $requiredType = null,
        ?string $requiredPhone = null,
        ?string $errorMessage = null,
        bool $strictMode = false
    ) {
        $this->requiredType = $requiredType;
        $this->requiredPhone = $requiredPhone;
        $this->errorMessage = $errorMessage ?? '验证码错误';
        $this->strictMode = $strictMode;
    }

    /**
     * 执行验证规则
     *
     * @param  string  $attribute  属性名
     * @param  mixed  $value  要验证的值（验证码）
     * @param  Closure  $fail  失败回调
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            // 获取验证数据
            $data = request()->all();

            // 验证基础数据
            if (! $this->validateBasicData($value, $data, $fail)) {
                return;
            }

            // 验证码验证
            if (! $this->validateCode($value, $data, $fail)) {
                return;
            }
        } catch (\Exception $e) {
            $fail('验证手机验证码时发生错误: ' . $e->getMessage());
        }
    }

    /**
     * 验证基础数据
     */
    protected function validateBasicData(mixed $value, array $data, Closure $fail): bool
    {
        // 检查验证码
        if (empty($value)) {
            $fail('验证码不能为空');

            return false;
        }

        if (! is_string($value)) {
            $fail('验证码必须是字符串');

            return false;
        }

        if (strlen($value) !== 6) {
            $fail('验证码必须是6位数字');

            return false;
        }

        // 检查必需的参数
        if ($this->strictMode) {
            if (empty($data['type'])) {
                $fail('验证码类型不能为空');

                return false;
            }

            if (empty($data['phone'])) {
                $fail('手机号不能为空');

                return false;
            }
        }

        return true;
    }

    /**
     * 验证手机验证码
     */
    protected function validateCode(mixed $value, array $data, Closure $fail): bool
    {
        $type = $data['type'] ?? null;
        $phone = $data['phone'] ?? null;

        // 严格模式下检查必需参数
        if ($this->strictMode) {
            if ($this->requiredType && $type !== $this->requiredType) {
                $fail('验证码类型不匹配');

                return false;
            }

            if ($this->requiredPhone && $phone !== $this->requiredPhone) {
                $fail('手机号不匹配');

                return false;
            }
        }

        // 调用短信服务验证
        try {
            $smsService = app(SmsService::class);

            if (! $smsService->verifyCode($type, $phone, $value)) {
                $fail($this->errorMessage);

                return false;
            }

            return true;
        } catch (LogicException $e) {
            $fail($e->getMessage());

            return false;
        }
    }

    /**
     * 静态创建方法 - 基础验证
     */
    public static function make(): self
    {
        return new self;
    }

    /**
     * 静态创建方法 - 严格模式
     *
     * @param  string  $type  验证码类型
     * @param  string  $phone  手机号
     */
    public static function strict(string $type, string $phone): self
    {
        return new self($type, $phone, '验证码错误或已过期', true);
    }

    /**
     * 静态创建方法 - 自定义错误消息
     *
     * @param  string  $errorMessage  错误消息
     */
    public static function message(string $errorMessage): self
    {
        return new self(null, null, $errorMessage);
    }

    /**
     * 获取验证规则参数
     */
    public function getParameters(): array
    {
        return [
            'required_type' => $this->requiredType,
            'required_phone' => $this->requiredPhone,
            'error_message' => $this->errorMessage,
            'strict_mode' => $this->strictMode,
        ];
    }

    /**
     * 设置错误消息
     */
    public function setErrorMessage(string $message): self
    {
        $this->errorMessage = $message;

        return $this;
    }

    /**
     * 设置严格模式
     */
    public function setStrictMode(bool $strict): self
    {
        $this->strictMode = $strict;

        return $this;
    }
}
