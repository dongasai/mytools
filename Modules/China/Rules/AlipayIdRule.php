<?php

namespace Modules\China\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * 支付宝ID验证规则
 *
 * 重构自 AlipayIdValidator，实现Laravel 12的ValidationRule接口
 * 验证支付宝ID格式：支持11位手机号或邮箱地址
 */
class AlipayIdRule implements ValidationRule
{
    /**
     * 正则表达式模式
     */
    private string $pattern;

    /**
     * 错误消息
     */
    private string $errorMessage;

    /**
     * 构造函数
     *
     * @param  string|null  $pattern  自定义正则表达式，默认支持手机号和邮箱
     * @param  string|null  $errorMessage  自定义错误消息
     */
    public function __construct(?string $pattern = null, ?string $errorMessage = null)
    {
        $this->pattern = $pattern ?? '/^(?:\d{11}|[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})$/';
        $this->errorMessage = $errorMessage ?? '必须是有效的11位手机号或邮箱地址';
    }

    /**
     * 执行验证规则
     *
     * @param  string  $attribute  属性名
     * @param  mixed  $value  要验证的值
     * @param  Closure  $fail  失败回调
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            // 验证基础数据
            if (! $this->validateBasicData($value, $fail)) {
                return;
            }

            // 验证格式
            if (! $this->validateFormat($value, $fail)) {
                return;
            }

        } catch (\Exception $e) {
            $fail('验证支付宝ID时发生错误: '.$e->getMessage());
        }
    }

    /**
     * 验证基础数据
     */
    protected function validateBasicData(mixed $value, Closure $fail): bool
    {
        if (empty($value)) {
            $fail('支付宝ID不能为空');

            return false;
        }

        if (! is_string($value)) {
            $fail('支付宝ID必须是字符串');

            return false;
        }

        return true;
    }

    /**
     * 验证格式
     */
    protected function validateFormat(mixed $value, Closure $fail): bool
    {
        // 使用正则表达式进行匹配
        if (preg_match($this->pattern, $value)) {
            return true;
        }

        $fail($this->errorMessage);

        return false;
    }

    /**
     * 验证是否为手机号格式
     */
    public function isPhoneNumber(string $value): bool
    {
        return preg_match('/^\d{11}$/', $value) === 1;
    }

    /**
     * 验证是否为邮箱格式
     */
    public function isEmail(string $value): bool
    {
        return preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $value) === 1;
    }

    /**
     * 获取验证规则参数
     */
    public function getParameters(): array
    {
        return [
            'pattern' => $this->pattern,
            'error_message' => $this->errorMessage,
        ];
    }

    /**
     * 设置自定义错误消息
     */
    public function setErrorMessage(string $message): self
    {
        $this->errorMessage = $message;

        return $this;
    }

    /**
     * 设置自定义正则表达式
     */
    public function setPattern(string $pattern): self
    {
        $this->pattern = $pattern;

        return $this;
    }

    /**
     * 静态创建方法 - 仅支持手机号
     */
    public static function phone(): self
    {
        return new self('/^\d{11}$/', '必须是有效的11位手机号');
    }

    /**
     * 静态创建方法 - 仅支持邮箱
     */
    public static function email(): self
    {
        return new self('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', '必须是有效的邮箱地址');
    }

    /**
     * 静态创建方法 - 支持手机号或邮箱
     */
    public static function make(): self
    {
        return new self;
    }
}
