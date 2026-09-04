<?php

namespace Modules\Demo5\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ChinesePhoneNumber implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('手机号必须是字符串');

            return;
        }

        // 移除所有非数字字符
        $phoneNumber = preg_replace('/\D/', '', $value);

        // 验证中国手机号格式
        if (! preg_match('/^1[3-9]\d{9}$/', $phoneNumber)) {
            $fail('请输入有效的中国手机号码');

            return;
        }

        // 检查是否为已知的无效号码段
        $invalidSegments = [
            '174', '1740', '1741', '1742', '1743', '1744', '1745', '1746', '1747', '1748', '1749', // 卫星通信
            '141', '142', '143', '144', '145', '146', '147', // 物联网号段
        ];

        $segment = substr($phoneNumber, 0, 3);
        $extendedSegment = substr($phoneNumber, 0, 4);

        if (in_array($segment, $invalidSegments) || in_array($extendedSegment, $invalidSegments)) {
            $fail('该手机号码段不支持');

            return;
        }
    }
}
