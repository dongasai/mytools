<?php

namespace Modules\Demo5\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPassword implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('密码必须是字符串');

            return;
        }

        // 检查密码长度
        if (strlen($value) < 8) {
            $fail('密码长度至少8位');

            return;
        }

        // 检查是否包含大写字母
        if (! preg_match('/[A-Z]/', $value)) {
            $fail('密码必须包含至少一个大写字母');

            return;
        }

        // 检查是否包含小写字母
        if (! preg_match('/[a-z]/', $value)) {
            $fail('密码必须包含至少一个小写字母');

            return;
        }

        // 检查是否包含数字
        if (! preg_match('/[0-9]/', $value)) {
            $fail('密码必须包含至少一个数字');

            return;
        }

        // 检查是否包含特殊字符
        if (! preg_match('/[!@#$%^&*(),.?":{}|<>]/', $value)) {
            $fail('密码必须包含至少一个特殊字符');

            return;
        }

        // 检查是否包含常见弱密码
        $weakPasswords = [
            'password', '123456', '12345678', 'qwerty', 'abc123',
            'password123', 'admin', 'letmein', 'welcome', 'monkey',
        ];

        if (in_array(strtolower($value), $weakPasswords)) {
            $fail('密码过于简单，请使用更复杂的密码');

            return;
        }
    }
}
