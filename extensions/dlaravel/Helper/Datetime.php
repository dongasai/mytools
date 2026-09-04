<?php

namespace DLaravel\Helper;

use Carbon\Carbon;

class Datetime
{
    /**
     * 秒转时间
     *
     * @return string
     */
    public static function ss($seconds)
    {

        //        $seconds = 86461; // 例如86461秒，可自行设置不同秒数
        $carbonInstance = Carbon::createFromTimestamp($seconds);
        $humanReadableString = $carbonInstance->diffForHumans(null, true, true);

        return $humanReadableString;
    }

    /**
     * 时间戳 转 字符串
     *
     * @return string
     */
    public static function ts2string($ts)
    {
        return Carbon::createFromTimestamp($ts, config('app.timezone'))->toDateTimeString();

    }
}
