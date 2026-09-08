<?php

namespace Modules\AClean\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * 验证清理类型规则
 */
class ValidCleanupType implements ValidationRule
{
    /**
     * 验证规则
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $validTypes = [1, 2, 3, 4, 5]; // 1清空表,2删除所有,3按时间删除,4按用户删除,5按条件删除

        if (!in_array($value, $validTypes)) {
            $fail('清理类型无效，必须是: 1清空表, 2删除所有, 3按时间删除, 4按用户删除, 5按条件删除');
        }
    }
}