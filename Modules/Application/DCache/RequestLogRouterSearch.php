<?php

namespace Modules\Application\DCache;

use DLaravel\Model\RequestLog;
use DLaravel\DCache\DCacheBase;

/**
 * 请求日志路由搜索缓存
 */
class RequestLogRouterSearch extends DCacheBase
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
     * @param array $parameter 参数，格式为 ['app', 'oap'] 指定模块前缀，默认为 ['app', 'oap']
     * @return array 路由列表，格式为 ['router' => 'router', ...]
     */
    public static function getNewData(array $parameter = []): array
    {
        // 参数验证：确保传入模块前缀列表，默认值提供向后兼容
        $modules = $parameter ?? ['app', 'oap'];

        $data = RequestLog::query()
            ->distinct('router')
            ->pluck('router')
            ->toArray();

        $res = [];
        foreach ($data as $router) {
            // 只保留指定模块前缀的路由
            if (in_array(substr($router, 0, 3), $modules)) {
                $res[$router] = $router;
            }
        }

        return $res;
    }
}