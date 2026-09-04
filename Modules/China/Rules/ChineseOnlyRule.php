<?php

namespace Modules\China\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * 中文字符验证规则
 *
 * 重构自 IsChineseOnlyValidator，实现Laravel 12的ValidationRule接口
 * 验证字符串是否完全由中文字符组成
 */
class ChineseOnlyRule implements ValidationRule
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
     * 是否允许空值
     */
    private bool $allowEmpty;

    /**
     * 构造函数
     *
     * @param  string|null  $pattern  自定义正则表达式，默认匹配中文字符
     * @param  string|null  $errorMessage  自定义错误消息
     * @param  bool  $allowEmpty  是否允许空值，默认不允许
     */
    public function __construct(?string $pattern = null, ?string $errorMessage = null, bool $allowEmpty = false)
    {
        $this->pattern = $pattern ?? '/^[\x{4e00}-\x{9fa5}]+$/u';
        $this->errorMessage = $errorMessage ?? '只能包含中文字符';
        $this->allowEmpty = $allowEmpty;
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

            // 验证中文字符
            if (! $this->validateChineseCharacters($value, $fail)) {
                return;
            }

        } catch (\Exception $e) {
            $fail('验证中文字符时发生错误: '.$e->getMessage());
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
            $fail('不能为空');

            return false;
        }

        if (! is_string($value)) {
            $fail('必须是字符串');

            return false;
        }

        return true;
    }

    /**
     * 验证中文字符
     */
    protected function validateChineseCharacters(mixed $value, Closure $fail): bool
    {
        // 使用正则表达式检查字符串是否完全匹配中文字符
        if (preg_match($this->pattern, $value)) {
            return true;
        }

        $fail($this->errorMessage);

        return false;
    }

    /**
     * 统计中文字符数量
     */
    public function countChineseCharacters(string $value): int
    {
        // 使用正则表达式匹配所有中文字符
        preg_match_all('/[\x{4e00}-\x{9fa5}]/u', $value, $matches);

        return count($matches[0]);
    }

    /**
     * 检查字符串是否包含中文字符
     */
    public function containsChinese(string $value): bool
    {
        return preg_match('/[\x{4e00}-\x{9fa5}]/u', $value) > 0;
    }

    /**
     * 提取中文字符
     */
    public function extractChinese(string $value): string
    {
        preg_match_all('/[\x{4e00}-\x{9fa5}]/u', $value, $matches);

        return implode('', $matches[0]);
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

    /**
     * 静态创建方法 - 不允许空值
     */
    public static function make(): self
    {
        return new self;
    }

    /**
     * 静态创建方法 - 允许空值
     */
    public static function allowEmpty(): self
    {
        return new self(null, null, true);
    }

    /**
     * 静态创建方法 - 仅包含中文字符和空值
     */
    public static function chineseOrEmpty(): self
    {
        return new self(null, '只能包含中文字符或为空', true);
    }
}
