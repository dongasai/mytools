<?php

namespace Modules\Application\DCache;

use DLaravel\Model\RequestLog;
use DLaravel\DCache\DCacheBase;

/**
 * 请求日志路径缓存
 */
class RequestLogPath extends DCacheBase
{
    /**
     * 缓存时间(秒)
     */
    public static function getTtl(): int
    {
        return 3600 * 24; // 24小时
    }

    /**
     * 防重复执行时间(秒)
     */
    public static function getPreventDuplication(): int
    {
        return 3600; // 1小时
    }

    /**
     * 获取新数据
     *
     * @param array $parameter 参数（未使用）
     * @return array
     */
    public static function getNewData(array $parameter = []): mixed
    {
        $data = RequestLog::query()
            ->groupBy('path')
            ->distinct()
            ->where('module', '!=', 'temp')
            ->pluck('path', 'path')
            ->toArray();

        return $data;
    }
}