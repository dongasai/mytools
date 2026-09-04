<?php

namespace DLaravel\Helper;

/**
 * 缓存助手类
 */
class Cache
{
    /**
     * 回调函数的缓存
     *
     * @param  $key
     * @param  int  $exp  过期秒数
     * @return mixed
     */
    public static function cacheCall($key2, callable $callable, $param_arr, $exp = 60, $tags = [])
    {
        $key = self::getKey($key2);
        if ($exp) {
            $old = \Illuminate\Support\Facades\Cache::get($key);
            if (! is_null($old)) {
                return $old;
            }
        }

        $new = call_user_func_array($callable, $param_arr);
        if ($exp) {
            \Illuminate\Support\Facades\Cache::put($key, $new, $exp);
        }

        if ($tags) {
            CacheTag::key_tags($key, $tags, null);
        }

        return $new;
    }

    /**
     * 设置数据
     *
     * @return void
     */
    public static function put($key, $data, $exp = 60, $tags = [])
    {
        $key = self::getKey($key);
        \Illuminate\Support\Facades\Cache::put($key, $data, $exp);
        if ($tags) {
            CacheTag::key_tags($key, $tags, null);
        }

    }

    /**
     * 获取数据
     *
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        $key = self::getKey($key);
        Logger::debug('Cache-get'.$key);

        return \Illuminate\Support\Facades\Cache::get($key, $default);
    }

    /**
     * has数据
     *
     * @return bool
     */
    public static function has($key)
    {
        $key = self::getKey($key);

        return \Illuminate\Support\Facades\Cache::has($key);
    }

    public static function getTags($key)
    {
        $key = self::getKey($key);

    }

    /**
     * 获取键名
     *
     * @return string
     */
    public static function getKey($data)
    {
        return md5(serialize($data));
    }
}
