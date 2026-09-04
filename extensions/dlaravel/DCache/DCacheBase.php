<?php

namespace DLaravel\DCache;

use DLaravel\Helper\Cache;

/**
 * DCache 缓存基类
 *
 * 提供缓存管理的通用功能：
 * - 缓存获取和存储
 * - 防重复机制
 * - TTL 管理
 * - 自动缓存键生成
 */
abstract class DCacheBase implements DCacheInterface
{
    
    /**
     * 获取缓存数据（核心方法）
     *
     * @param array $parameter 参数
     * @param bool $force 是否强制刷新
     * @return mixed
     */
    public static function getData(array $parameter = [], bool $force = false): mixed
    {
        $key = self::getKey($parameter);
        $keyPD = $key . '_PD';

        // 防重复检查
        if (!$force && Cache::has($keyPD)) {
            $data = Cache::get($key);
            if (!empty($data)) {
                return $data;
            }
        }

        // 获取新数据
        $data = self::getNewData($parameter);

        // 存储缓存
        Cache::put($key, $data, self::getTtl());
        Cache::put($keyPD, 1, self::getPreventDuplication());

        return $data;
    }

    /**
     * 获取缓存键
     *
     * @param array $parameter 参数
     * @return string
     */
    protected static function getKey(array $parameter): string
    {
        return md5(serialize([static::class, $parameter]));
    }

    /**
     * 清除缓存
     *
     * @param array $parameter 参数
     * @return void
     */
    public static function clearCache(array $parameter = []): void
    {
        $key = self::getKey($parameter);
        $keyPD = $key . '_PD';

        // 删除缓存和防重复标记
        Cache::put($key, null, -1);
        Cache::put($keyPD, null, -1);
    }

    /**
     * 强制刷新缓存
     *
     * @param array $parameter 参数
     * @return mixed
     */
    public static function refreshCache(array $parameter = []): mixed
    {
        return self::getData($parameter, true);
    }
}