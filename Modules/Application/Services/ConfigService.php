<?php

namespace Modules\Application\Services;

use DLaravel\Helper\Cache;
use DLaravel\Helper\CacheTag;

use Modules\AFile\Img;
use Modules\Application\Enums\CONFIG_TYPE;
use Modules\Application\Models\ApplicationConfig;

class ConfigService
{
    const CACHE_TAG = 'config';

    /**
     * 获取最后配置时间
     *
     * @return int
     */
    public static function getConfigTime()
    {
        return \DLaravel\Helper\Cache::cacheCall('app-config-time', function () {
            return time();
        }, [], 1000);

    }

    public static function setConfigTime()
    {
        \Illuminate\Support\Facades\Cache::put('app-config-time', time());
    }

    /**
     * 默认
     *
     * @return int|mixed|string|null
     */
    public static function getValueDefault($key, $default = null)
    {
        $value = self::getValue($key);
        $value = $value ?? $default;
        Trace::applyData('config-read', [$key, $value]);

        return $value;
    }

    /**
     * 获取二维数据
     *
     * @return mixed|string|null
     */
    public static function getValue2($key, $key2)
    {
        $value = self::getValue($key);

        return $value[$key2] ?? null;
    }

    /**
     * @return mixed
     */
    public static function getValueDefaultCache($key, $default = null, $ttl = 60)
    {
        $key2 = 'config-key-'.$key;
        $old = \Illuminate\Support\Facades\Cache::get($key2, function () use ($key, $default, $ttl) {
            $new = self::getValueDefault($key, $default);
            $key2 = 'config-key-'.$key;

            \Illuminate\Support\Facades\Cache::put($key2, $new, $ttl);

            return $new;
        });

        return $old;
    }

    /**
     * 获取值
     *
     * @return int|string|null
     */
    private static function getValue($key, $model = null)
    {
        if (! $model) {
            /**
             * @var ApplicationConfig $model
             */
            $model = ApplicationConfig::query()->where(
                [
                    'keyname' => $key,
                ]
            )->first();
        }

        if ($model) {
            //            dump($model);
            if ($model->type === CONFIG_TYPE::TYPE_INT->valueInt()) {
                return (int) $model->value;
            }
            if ($model->type === CONFIG_TYPE::TYPE_FLOAT->valueInt()) {
                return (float) $model->value;
            }
            if ($model->type === CONFIG_TYPE::TYPE_TIME->valueInt()) {
                return (int) $model->value;
            }
            if ($model->type === CONFIG_TYPE::TYPE_IMG->valueInt()) {
                return Img::getAdminPicUrl($model->value);
            }
            if ($model->type === CONFIG_TYPE::TYPE_BOOL->valueInt()) {
                return boolval($model->value);
            }
            if ($model->type === CONFIG_TYPE::TYPE_IS->valueInt()) {
                return boolval($model->value);
            }
            if ($model->type === CONFIG_TYPE::TYPE_JSON->valueInt()) {
                return json_decode($model->value, true);
            }
            if ($model->type === CONFIG_TYPE::TYPE_EMBEDS->valueInt()) {
                return json_decode($model->value, true);
            }

            return $model->value;
        }

        return null;
    }

    /**
     * 获取分组kv
     *
     * @return mixed
     */
    public static function getGroupKv()
    {
        return Cache::cacheCall([__FUNCTION__, __CLASS__, 1], function () {

            $data = ApplicationConfig::query()->distinct()->pluck('group', 'group')->toArray();

            return $data;
        }, [], 3600, [self::CACHE_TAG]);

    }

    public static function getGroupKv2($group)
    {
        return Cache::cacheCall([__FUNCTION__, __CLASS__, $group, 2], function ($group) {

            $data = ApplicationConfig::query()->where('group', $group)

                ->distinct()
                ->pluck('group2', 'group2')
                ->toArray();

            //            dd($data);
            return $data;
        }, [$group], 3600, [self::CACHE_TAG]);

    }

    /**
     * 获取客户端的可用
     *
     * @return mixed
     */
    public static function getClient()
    {
        return Cache::cacheCall([__FUNCTION__, __CLASS__], function () {

            $data = ApplicationConfig::query()->where('is_client', '=', '1')->get();
            $res = [];
            foreach ($data as $item) {
                $res[$item->keyname] = self::getValue($item->keyname, $item);
            }

            return $res;
        }, [], 1, [self::CACHE_TAG]);

    }

    /**
     * 获取分组的配置想
     *
     * @return mixed
     */
    public static function getByGroup($group)
    {
        return Cache::cacheCall([__FUNCTION__, __CLASS__], function ($group) {

            $data = ApplicationConfig::query()->where('group', '=', $group)->get();
            $res = [];
            foreach ($data as $item) {
                $res[$item->keyname] = self::getValue($item->keyname, $item);
            }

            return $res;
        }, [$group], 10, [self::CACHE_TAG]);
    }

    public static function clear_cache()
    {
        CacheTag::tags_clear([self::CACHE_TAG]);
    }
}
