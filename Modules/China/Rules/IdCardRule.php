<?php

namespace Modules\China\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * 身份证号验证规则
 *
 * 重构自 IdCardValidator，实现Laravel 12的ValidationRule接口
 * 验证中国身份证号格式：18位数字（最后一位可能是X）
 */
class IdCardRule implements ValidationRule
{
    /**
     * 身份证号长度要求
     */
    private int $requiredLength;

    /**
     * 是否支持最后一位为X
     */
    private bool $allowLastCharX;

    /**
     * 错误消息
     */
    private string $errorMessage;

    /**
     * 构造函数
     *
     * @param  int  $requiredLength  身份证号长度要求，默认18位
     * @param  bool  $allowLastCharX  是否允许最后一位为X，默认允许
     * @param  string|null  $errorMessage  自定义错误消息
     */
    public function __construct(int $requiredLength = 18, bool $allowLastCharX = true, ?string $errorMessage = null)
    {
        $this->requiredLength = $requiredLength;
        $this->allowLastCharX = $allowLastCharX;
        $this->errorMessage = $errorMessage ?? "身份证号必须是{$requiredLength}位数字".($allowLastCharX ? '（最后一位可以是X）' : '');
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

            // 基础校验位验证（可选）
            if (! $this->validateChecksum($value, $fail)) {
                return;
            }

        } catch (\Exception $e) {
            $fail('验证身份证号时发生错误: '.$e->getMessage());
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
        if (strlen($value) != $this->requiredLength) {
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

        // 验证最后一位
        if ($this->allowLastCharX) {
            if (! ctype_digit($lastChar) && strtoupper($lastChar) !== 'X') {
                $fail('身份证号最后一位必须是数字或X');

                return false;
            }
        } else {
            if (! ctype_digit($lastChar)) {
                $fail('身份证号最后一位必须是数字');

                return false;
            }
        }

        return true;
    }

    /**
     * 基础校验位验证（简化版）
     */
    protected function validateChecksum(mixed $value, Closure $fail): bool
    {
        // 这里只做简单的格式验证，不进行复杂的校验位计算
        // 如需完整的身份证校验，可以实现校验位算法
        return true;
    }

    /**
     * 获取身份证号的出生日期
     *
     * @return string|null 格式：YYYY-MM-DD
     */
    public function extractBirthDate(string $idCard): ?string
    {
        if (strlen($idCard) >= 14) {
            $year = substr($idCard, 6, 4);
            $month = substr($idCard, 10, 2);
            $day = substr($idCard, 12, 2);

            if (checkdate($month, $day, $year)) {
                return "{$year}-{$month}-{$day}";
            }
        }

        return null;
    }

    /**
     * 获取身份证号的性别
     *
     * @return string|null 'male', 'female', or null
     */
    public function extractGender(string $idCard): ?string
    {
        if (strlen($idCard) >= 17) {
            $genderDigit = substr($idCard, 16, 1);

            return (int) $genderDigit % 2 === 1 ? 'male' : 'female';
        }

        return null;
    }

    /**
     * 获取验证规则参数
     */
    public function getParameters(): array
    {
        return [
            'required_length' => $this->requiredLength,
            'allow_last_char_x' => $this->allowLastCharX,
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
     * 静态创建方法 - 18位身份证，支持X结尾
     */
    public static function make(): self
    {
        return new self;
    }

    /**
     * 静态创建方法 - 仅数字身份证
     */
    public static function digitsOnly(): self
    {
        return new self(18, false, '身份证号必须是18位数字');
    }
}
