<?php

namespace Modules\ABase\Support;

use DLaravel\Helper\CacheTag;
use DLaravel\Helper\Logger;

/**
 * 缓存辅助类
 *
 * 提供统一的缓存操作接口，封装键名生成、回调缓存、标签管理等功能
 */
class Cache
{
    /**
     * 回调函数的缓存
     *
     * 如果缓存不存在则执行回调函数并缓存结果
     *
     * @param  mixed  $key2  缓存键标识（任意可序列化值）
     * @param  callable  $callable  回调函数
     * @param  array  $param_arr  回调函数参数数组
     * @param  int  $exp  过期时间（秒），默认60秒
     * @param  array  $tags  缓存标签数组
     * @return mixed 回调函数的返回值
     */
    public static function cacheCall(mixed $key2, callable $callable, array $param_arr, int $exp = 60, array $tags = []): mixed
    {
        $key = self::getKey($key2);
        $old = \Illuminate\Support\Facades\Cache::get($key);
        if (! is_null($old)) {
            return $old;
        }
        $new = call_user_func_array($callable, $param_arr);
        \Illuminate\Support\Facades\Cache::put($key, $new, $exp);
        if ($tags) {
            CacheTag::key_tags($key, $tags, null);
        }

        return $new;
    }

    /**
     * 设置缓存数据
     *
     * @param  mixed  $key  缓存键标识
     * @param  mixed  $data  缓存数据
     * @param  int  $exp  过期时间（秒），默认60秒
     * @param  array  $tags  缓存标签数组
     * @return void
     */
    public static function put(mixed $key, mixed $data, int $exp = 60, array $tags = []): void
    {
        $key = self::getKey($key);
        \Illuminate\Support\Facades\Cache::put($key, $data, $exp);
        if ($tags) {
            CacheTag::key_tags($key, $tags, null);
        }
    }

    /**
     * 获取缓存数据
     *
     * @param  mixed  $key  缓存键标识
     * @param  mixed  $default  默认值（缓存不存在时返回）
     * @return mixed
     */
    public static function get(mixed $key, mixed $default = null): mixed
    {
        $key = self::getKey($key);
        Logger::debug('Cache-get' . $key);

        return \Illuminate\Support\Facades\Cache::get($key, $default);
    }

    /**
     * 检查缓存是否存在
     *
     * @param  mixed  $key  缓存键标识
     * @return bool
     */
    public static function has(mixed $key): bool
    {
        $key = self::getKey($key);

        return \Illuminate\Support\Facades\Cache::has($key);
    }

    /**
     * 获取缓存键关联的标签
     *
     * @param  mixed  $key  缓存键标识
     * @return void
     */
    public static function getTags(mixed $key): void
    {
        $key = self::getKey($key);
    }

    /**
     * 生成缓存键名
     *
     * 通过序列化和MD5生成唯一的缓存键名
     *
     * @param  mixed  $data  任意可序列化数据
     * @return string MD5格式的缓存键名
     */
    public static function getKey(mixed $data): string
    {
        return md5(serialize($data));
    }
}
