<?php

namespace DLaravel\Helper;

class CacheTag
{
    /**
     * 缓存增加标签
     *
     * @return void
     */
    public static function key_tags($key, $tags, $ttl)
    {
        foreach ($tags as $tag) {
            self::key_tag($key, $tag, $ttl);
        }

    }

    /**
     * 缓存标签处理
     *
     * @return void
     */
    public static function key_tag($key, $tag, $ttl)
    {
        $olds = \Illuminate\Support\Facades\Cache::get('cache_tag_'.$tag, []);
        if (! in_array($key, $olds)) {
            $olds[] = $key;
            \Illuminate\Support\Facades\Cache::put('cache_tag_'.$tag, $olds, $ttl);

        }
        if (count($olds) > 300) {
            // 单标签超过300个键
            Logger::error("cache tags  $tag lenght > 300");
        }

    }

    /**
     * 清空标签对应的缓存
     */
    public static function tags_clear($tags, $delete = false)
    {
        foreach ($tags as $tag) {
            $olds = \Illuminate\Support\Facades\Cache::get('cache_tag_'.$tag, []);
            //            dd($olds,$tag);
            foreach ($olds as $key) {
                \Illuminate\Support\Facades\Cache::delete($key);
            }
            if ($delete) {
                \Illuminate\Support\Facades\Cache::delete('cache_tag_'.$tag);
            }
        }

    }
}
