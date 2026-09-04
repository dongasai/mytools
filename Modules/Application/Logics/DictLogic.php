<?php

namespace Modules\Application\Logics;

use Modules\Application\Services\DictService;

/**
 * 字典逻辑层
 *
 * 提供静态方法封装,无状态,高性能
 */
class DictLogic
{
    /**
     * 禁止实例化
     */
    private function __construct()
    {
    }
    /**
     * 获取字典列表
     *
     * @param string $dictType 字典类型
     * @return array
     */
    public static function getList(string $dictType): array
    {
        return DictService::getList($dictType);
    }

    /**
     * 获取字典标签
     *
     * @param string $dictType 字典类型
     * @param string $dictValue 字典值
     * @return string|null
     */
    public static function getLabel(string $dictType, string $dictValue): ?string
    {
        return DictService::getLabel($dictType, $dictValue);
    }

    /**
     * 批量获取字典
     *
     * @param array $dictTypes 字典类型数组
     * @return array
     */
    public static function getBatch(array $dictTypes): array
    {
        return DictService::getBatch($dictTypes);
    }

    /**
     * 批量获取字典（包含extra字段）
     *
     * @param array $dictTypes 字典类型数组
     * @return array
     */
    public static function getBatchWithExtra(array $dictTypes): array
    {
        return DictService::getBatchWithExtra($dictTypes);
    }
}
