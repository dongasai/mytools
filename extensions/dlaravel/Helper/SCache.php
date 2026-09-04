<?php

namespace DLaravel\Helper;

use DLaravel\Entity\CacheItem;
use DLaravel\Helper\CacheTag;
use DLaravel\Helper\Logger;

/**
 * SCache - symfony/cache 使用 Laravel Cache 的封装
 *
 * 提供基于 CacheItem 的缓存存储和获取
 */
class SCache
{
    /**
     * 设置数据
     *
     * @param mixed $key 键名或数据
     * @param mixed $data 数据或 CacheItem 对象
     * @param int $exp 过期时间(秒)
     * @param array $tags 缓存标签
     * @return CacheItem
     */
    public static function put($key, $data, $exp = 60, $tags = [])
    {
        $key = self::getKey($key);
        if ($data instanceof CacheItem) {
            $d2 = $data;
        } else {
            $d2 = new CacheItem($key, $data, time(), $exp);
        }

        \Illuminate\Support\Facades\Cache::put($key, $d2, $exp);
        if ($tags) {
            CacheTag::key_tags($key, $tags, null);
        }

        return $d2;
    }

    /**
     * 获取数据
     *
     * @param mixed $key 键名或数据
     * @param mixed $default 默认值
     * @return CacheItem
     */
    public static function get($key, $default = null)
    {
        $key = self::getKey($key);
        Logger::debug('Cache-get' . $key);
        $res = \Illuminate\Support\Facades\Cache::get($key, $default);
        if ($res instanceof CacheItem) {
            Logger::debug('Cache-get hit' . $key);
            $res->isHit();

            return $res;
        }
        $res = new CacheItem($key, $default, 0);
        $res->setHit(false)->expiresAfter(-1);
        Logger::debug('Cache-get no hit' . $key);

        return $res;
    }

    /**
     * 获取数据值
     *
     * @param mixed $key 键名或数据
     * @param mixed $default 默认值
     * @return mixed|null
     */
    public static function getValue($key, $default = null)
    {
        $item = self::get($key, $default);

        return $item->getValue();
    }

    /**
     * 检查缓存是否存在
     *
     * @param mixed $key 键名或数据
     * @return bool
     */
    public static function has($key)
    {
        $key = self::getKey($key);

        return \Illuminate\Support\Facades\Cache::has($key);
    }

    /**
     * 获取缓存标签
     *
     * @param mixed $key 键名或数据
     */
    public static function getTags($key)
    {
        $key = self::getKey($key);
    }

    /**
     * 获取键名
     *
     * @param mixed $data 数据或字符串键名
     * @return string
     */
    public static function getKey($data)
    {
        if (is_string($data)) {
            return $data;
        }

        return md5(serialize($data));
    }
}