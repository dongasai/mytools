<?php

namespace Modules\China\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * 中国身份证号码验证规则
 *
 * 重构自 CNIdCardValidator，实现Laravel 12的ValidationRule接口
 * 验证中国18位身份证号码
 */
class CNIdCardRule implements ValidationRule
{
    /**
     * 身份证号长度要求
     */
    private int $requiredLength;

    /**
     * 错误消息
     */
    private string $errorMessage;

    /**
     * 构造函数
     *
     * @param  int  $requiredLength  身份证号长度要求，默认18位
     * @param  string|null  $errorMessage  自定义错误消息
     */
    public function __construct(int $requiredLength = 18, ?string $errorMessage = null)
    {
        $this->requiredLength = $requiredLength;
        $this->errorMessage = $errorMessage ?? "身份证号长度必须为{$requiredLength}位";
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

            // 验证长度
            if (! $this->validateLength($value, $fail)) {
                return;
            }

            // 验证字符格式
            if (! $this->validateCharacters($value, $fail)) {
                return;
            }

        } catch (\Exception $e) {
            $fail('验证中国身份证号时发生错误: '.$e->getMessage());
        }
    }

    /**
     * 验证基础数据
     */
    protected function validateBasicData(mixed $value, Closure $fail): bool
    {
        if (empty($value)) {
            $fail('身份证号不能为空');

            return false;
        }

        if (! is_string($value)) {
            $fail('身份证号必须是字符串');

            return false;
        }

        return true;
    }

    /**
     * 验证长度
     */
    protected function validateLength(mixed $value, Closure $fail): bool
    {
        if (strlen($value) !== $this->requiredLength) {
            $fail($this->errorMessage);

            return false;
        }

        return true;
    }

    /**
     * 验证字符格式
     */
    protected function validateCharacters(mixed $value, Closure $fail): bool
    {
        $string = (string) $value;

        // 获取除最后一位外的字符
        $prefix = substr($string, 0, -1);
        $lastChar = substr($string, -1);

        // 验证前17位必须是数字
        if (! ctype_digit($prefix)) {
            $fail('身份证号前17位必须是数字');

            return false;
        }

        // 验证最后一位可以是数字或X
        if (! ctype_digit($lastChar) && strtoupper($lastChar) !== 'X') {
            $fail('身份证号最后一位必须是数字或X');

            return false;
        }

        return true;
    }

    /**
     * 获取验证规则参数
     */
    public function getParameters(): array
    {
        return [
            'required_length' => $this->requiredLength,
            'error_message' => $this->errorMessage,
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
     * 静态创建方法
     */
    public static function make(): self
    {
        return new self;
    }
}
