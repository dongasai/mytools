<?php

namespace Modules\Application\Services;

use DLaravel\Helper\Cache;
use DLaravel\Helper\CacheTag;
use Modules\Application\Models\ApplicationDict;

/**
 * 字典缓存服务
 *
 * 提供字典数据的缓存读取，缓存标签 dict，TTL 86400秒（1天）
 */
class DictService
{
    const CACHE_TAG = 'dict';

    /**
     * 获取指定类型的字典列表（缓存1天=86400秒）
     *
     * @param string $dictType 字典类型
     * @return array 格式: [['label' => '男', 'value' => '1', 'sort' => 0], ...]
     */
    public static function getList(string $dictType): array
    {
        return Cache::cacheCall(
            [__FUNCTION__, __CLASS__, $dictType],
            function ($dictType) {
                $data = ApplicationDict::query()
                    ->where('dict_type', $dictType)
                    ->where('status', 1)
                    ->where('is_client', 1)
                    ->orderBy('dict_sort')
                    ->get();
                $res = [];
                foreach ($data as $item) {
                    $res[] = [
                        'label' => $item->dict_label,
                        'value' => $item->dict_value,
                        'sort'  => $item->dict_sort,
                    ];
                }
                return $res;
            },
            [$dictType],
            86400,
            [self::CACHE_TAG]
        );
    }

    /**
     * 根据 dict_value 获取 dict_label
     *
     * @param string $dictType 字典类型
     * @param string $dictValue 字典值
     * @return string|null 字典标签，未找到返回 null
     */
    public static function getLabel(string $dictType, string $dictValue): ?string
    {
        $list = self::getList($dictType);
        foreach ($list as $item) {
            if ($item['value'] === $dictValue) {
                return $item['label'];
            }
        }
        return null;
    }

    /**
     * 批量获取多个字典类型
     *
     * @param array $dictTypes 字典类型列表
     * @return array 格式: ['gender' => [...], 'user_status' => [...]]
     */
    public static function getBatch(array $dictTypes): array
    {
        $res = [];
        foreach ($dictTypes as $type) {
            $res[$type] = self::getList($type);
        }
        return $res;
    }

    /**
     * 获取指定类型的字典列表（包含extra字段）
     *
     * @param string $dictType 字典类型
     * @return array 格式: [['label' => '男', 'value' => '1', 'sort' => 0, 'extra' => '{...}'], ...]
     */
    public static function getListWithExtra(string $dictType): array
    {
        return Cache::cacheCall(
            [__FUNCTION__, __CLASS__, $dictType],
            function ($dictType) {
                $data = ApplicationDict::query()
                    ->where('dict_type', $dictType)
                    ->where('status', 1)
                    ->where('is_client', 1)
                    ->orderBy('dict_sort')
                    ->get(['dict_label', 'dict_value', 'dict_sort', 'remark']);
                $res = [];
                foreach ($data as $item) {
                    $res[] = [
                        'label' => $item->dict_label,
                        'value' => $item->dict_value,
                        'sort'  => $item->dict_sort,
                        'extra' => $item->remark ?? '',
                    ];
                }
                return $res;
            },
            [$dictType],
            86400,
            [self::CACHE_TAG]
        );
    }

    /**
     * 批量获取多个字典类型（包含extra字段）
     *
     * @param array $dictTypes 字典类型列表
     * @return array 格式: ['gas_type' => [...], 'n2o_factor' => [...]]
     */
    public static function getBatchWithExtra(array $dictTypes): array
    {
        $res = [];
        foreach ($dictTypes as $type) {
            $res[$type] = self::getListWithExtra($type);
        }
        return $res;
    }

    /**
     * 清除字典缓存
     *
     * @return void
     */
    public static function clearCache(): void
    {
        CacheTag::tags_clear([self::CACHE_TAG]);
    }
}
