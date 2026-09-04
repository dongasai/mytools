<?php

namespace Modules\Application\Logics;

use Modules\Application\Models\Feature;
use Modules\Application\Services\FeatureService;

/**
 * 功能开关逻辑层
 *
 * 提供静态方法封装,无状态,高性能
 */
class FeatureLogic
{
    /**
     * 快速判断功能是否开启
     *
     * @param int $userId 用户ID
     * @param string $featureKey 功能标识
     * @return bool
     */
    public static function check(int $userId, string $featureKey): bool
    {
        return FeatureService::isEnabledWithCache($userId, $featureKey);
    }

    /**
     * 获取用户可用功能键列表
     *
     * @param int $userId 用户ID
     * @return array
     */
    public static function getEnabledFeatureKeys(int $userId): array
    {
        $enabledFeatures = FeatureService::getUserEnabledFeatures($userId);
        return $enabledFeatures->pluck('key')->toArray();
    }

    /**
     * 批量判断多个功能开关
     *
     * @param int $userId 用户ID
     * @param array $featureKeys 功能标识数组
     * @return array ['feature_key' => bool]
     */
    public static function batchCheck(int $userId, array $featureKeys): array
    {
        $result = [];
        foreach ($featureKeys as $featureKey) {
            $result[$featureKey] = FeatureService::isEnabledWithCache($userId, $featureKey);
        }
        return $result;
    }

    /**
     * 获取用户所有功能状态(批量预加载)
     *
     * 用于客户端启动时一次性获取所有功能状态,减少API调用
     *
     * @param int $userId 用户ID
     * @return array ['feature_key' => bool]
     */
    public static function getAllFeaturesStatus(int $userId): array
    {
        $allFeatures = Feature::all();
        $result = [];

        foreach ($allFeatures as $feature) {
            $result[$feature->key] = FeatureService::isEnabledWithCache($userId, $feature->key);
        }

        return $result;
    }
}