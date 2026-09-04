<?php

namespace Modules\Application\DCache;

use DLaravel\Model\RequestLog;
use DLaravel\DCache\DCacheBase;

/**
 * 请求日志路由缓存
 */
class RequestLogRouter extends DCacheBase
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
     * @param array $parameter 参数
     * @return array
     */
    public static function getNewData(array $parameter = []): array
    {
        $data = RequestLog::query()
            ->groupBy('router')
            ->distinct()
            ->pluck('router', 'router')
            ->toArray();

        $res = [];
        foreach ($data as $k => $v) {
            if (in_array(substr($k, 0, 3), $parameter)) {
                $res[$k] = $k;
            }
        }

        return $res;
    }
}