<?php

namespace Modules\FeatureSms\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * 手机号验证规则
 *
 * 重构自 PhoneValidator，实现Laravel 12的ValidationRule接口
 * 验证手机号格式的有效性
 */
class PhoneRule implements ValidationRule
{
    /**
     * 手机号正则表达式模式
     */
    private string $pattern;

    /**
     * 错误消息
     */
    private string $errorMessage;

    /**
     * 是否允许空值
     */
    private bool $allowEmpty;

    /**
     * 验证模式（中国手机号、国际手机号、全部）
     */
    private string $mode;

    /**
     * 常用手机号正则模式
     */
    const PATTERN_CN = '/^1[3-9]\d{9}$/';           // 中国手机号

    const PATTERN_INTERNATIONAL = '/^\+?[1-9]\d{1,14}$/'; // 国际手机号

    const PATTERN_GENERIC = '/^[\d\+\-\(\)\s]+$/';   // 通用手机号格式

    /**
     * 验证模式常量
     */
    const MODE_CN = 'cn';

    const MODE_INTERNATIONAL = 'international';

    const MODE_ALL = 'all';

    /**
     * 构造函数
     *
     * @param  string|null  $pattern  自定义正则表达式
     * @param  string|null  $errorMessage  自定义错误消息
     * @param  bool  $allowEmpty  是否允许空值
     * @param  string  $mode  验证模式
     */
    public function __construct(
        ?string $pattern = null,
        ?string $errorMessage = null,
        bool $allowEmpty = false,
        string $mode = self::MODE_CN
    ) {
        $this->pattern = $pattern ?? $this->getPatternByMode($mode);
        $this->errorMessage = $errorMessage ?? '手机号格式不正确';
        $this->allowEmpty = $allowEmpty;
        $this->mode = $mode;
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

            // 验证手机号格式
            if (! $this->validatePhoneFormat($value, $fail)) {
                return;
            }
        } catch (\Exception $e) {
            $fail('验证手机号时发生错误: ' . $e->getMessage());
        }
    }

    /**
     * 验证基础数据
     */
    protected function validateBasicData(mixed $value, Closure $fail): bool
    {
        if (empty($value)) {
            if ($this->allowEmpty) {
                return true;
            }
            $fail('手机号不能为空');

            return false;
        }

        if (! is_string($value)) {
            $fail('手机号必须是字符串');

            return false;
        }

        return true;
    }

    /**
     * 验证手机号格式
     */
    protected function validatePhoneFormat(mixed $value, Closure $fail): bool
    {
        $phone = (string) $value;

        // 移除常见的分隔符
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone);

        if (preg_match($this->pattern, $phone)) {
            return true;
        }

        $fail($this->errorMessage);

        return false;
    }

    /**
     * 根据模式获取正则表达式
     */
    protected function getPatternByMode(string $mode): string
    {
        return match ($mode) {
            self::MODE_CN => self::PATTERN_CN,
            self::MODE_INTERNATIONAL => self::PATTERN_INTERNATIONAL,
            self::MODE_ALL => self::PATTERN_GENERIC,
            default => self::PATTERN_CN,
        };
    }

    /**
     * 格式化手机号
     */
    public function formatPhone(string $phone): string
    {
        // 移除所有非数字字符（保留+号）
        return preg_replace('/[^\d\+]/', '', $phone);
    }

    /**
     * 验证是否为中国手机号
     */
    public function isChinesePhone(string $phone): bool
    {
        return preg_match(self::PATTERN_CN, $this->formatPhone($phone)) > 0;
    }

    /**
     * 验证是否为国际手机号
     */
    public function isInternationalPhone(string $phone): bool
    {
        return preg_match(self::PATTERN_INTERNATIONAL, $this->formatPhone($phone)) > 0;
    }

    /**
     * 提取手机号中的纯数字
     */
    public function extractDigits(string $phone): string
    {
        return preg_replace('/[^\d]/', '', $phone);
    }

    /**
     * 静态创建方法 - 中国手机号验证
     */
    public static function chinese(): self
    {
        return new self(null, '请输入有效的中国手机号', false, self::MODE_CN);
    }

    /**
     * 静态创建方法 - 国际手机号验证
     */
    public static function international(): self
    {
        return new self(null, '请输入有效的国际手机号', false, self::MODE_INTERNATIONAL);
    }

    /**
     * 静态创建方法 - 允许空值
     */
    public static function optional(): self
    {
        return new self(null, '手机号格式不正确', true);
    }

    /**
     * 静态创建方法 - 自定义正则
     *
     * @param  string  $pattern  自定义正则表达式
     * @param  string  $errorMessage  错误消息
     */
    public static function regex(string $pattern, string $errorMessage = '手机号格式不正确'): self
    {
        return new self($pattern, $errorMessage);
    }

    /**
     * 获取验证规则参数
     */
    public function getParameters(): array
    {
        return [
            'pattern' => $this->pattern,
            'error_message' => $this->errorMessage,
            'allow_empty' => $this->allowEmpty,
            'mode' => $this->mode,
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
     * 设置是否允许空值
     */
    public function setAllowEmpty(bool $allowEmpty): self
    {
        $this->allowEmpty = $allowEmpty;

        return $this;
    }
}
