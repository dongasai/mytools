<?php

namespace Modules\Application\Services;

class OrderNo
{
    /**
     * 生成优雅可读的订单号
     * 格式: YYYYMMDD-HHMMSS-RRRR
     */
    public static function generate(): string
    {
        $date = date('Ymd-His');
        $random = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);

        return "{$date}-{$random}";
    }
}
