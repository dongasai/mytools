<?php

namespace Modules\Application\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Application\Events\FeatureChanged;
use Modules\Application\Exceptions\FeatureNotFoundException;
use Modules\Application\Models\Feature;
use Modules\Application\Models\FeatureBlacklist;
use Modules\Application\Models\FeatureWhitelist;
use Modules\Application\Models\UserFeatureSetting;

/**
 * 功能开关服务
 *
 * 提供功能开关的核心判断逻辑和管理功能
 *
 * 判断优先级:黑名单 > 白名单 > 灰度百分比 > 用户设置 > 默认状态
 */
class FeatureService
{
    /**
     * 通过ID获取功能定义
     *
     * @param int $featureId 功能ID
     * @return Feature|null
     */
    public static function getFeatureById(int $featureId): ?Feature
    {
        return Feature::find($featureId);
    }

    /**
     * 通过key获取功能定义
     *
     * @param string $featureKey 功能标识
     * @return Feature|null
     */
    public static function getFeatureByKey(string $featureKey): ?Feature
    {
        return Feature::where('key', $featureKey)->first();
    }

    /**
     * 判断用户是否拥有某个功能(核心判断逻辑)
     *
     * @param int $userId 用户ID
     * @param string $featureKey 功能标识
     * @return bool
     */
    public static function isEnabled(int $userId, string $featureKey): bool
    {
        // 获取功能定义
        $feature = self::getFeatureByKey($featureKey);
        if (!$feature) {
            return false; // 功能不存在,默认关闭
        }

        // 优先级1:检查黑名单(最高优先级)
        if (FeatureBlacklist::where('feature_id', $feature->id)
            ->where('user_id', $userId)
            ->exists()) {
            return false; // 黑名单强制关闭
        }

        // 优先级2:检查白名单(第二优先级)
        if (FeatureWhitelist::where('feature_id', $feature->id)
            ->where('user_id', $userId)
            ->exists()) {
            return true; // 白名单强制开启
        }

        // 优先级3:检查灰度百分比(新增,第三优先级)
        if ($feature->percentage > 0) {
            $hashValue = abs(crc32($userId));
            $modValue = $hashValue % 100;
            if ($modValue < $feature->percentage) {
                return true; // 落在灰度百分比范围内
            }
        }

        // 优先级4:检查用户个人设置(第四优先级)
        $userSetting = UserFeatureSetting::where('user_id', $userId)
            ->where('feature_id', $feature->id)
            ->first();
        if ($userSetting) {
            return $userSetting->is_enabled;
        }

        // 优先级5:返回功能默认状态(最低优先级)
        return $feature->is_enabled;
    }

    /**
     * 判断用户是否拥有某个功能(带缓存)
     *
     * 缓存分层:Redis → 数据库
     *
     * @param int $userId 用户ID
     * @param string $featureKey 功能标识
     * @return bool
     */
    public static function isEnabledWithCache(int $userId, string $featureKey): bool
    {
        $cacheKey = "user:{$userId}:feature:{$featureKey}";

        // Redis缓存
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            // 验证缓存值合法性
            if ($cached !== 'true' && $cached !== 'false') {
                Log::error('缓存值异常: ' . $cacheKey . ' = ' . $cached);
                Cache::forget($cacheKey); // 清除异常缓存
                return self::isEnabled($userId, $featureKey); // 降级数据库
            }
            return $cached === 'true';
        }

        // 查询数据库
        $enabled = self::isEnabled($userId, $featureKey);

        // 写入缓存,有效期1小时 + 随机偏移(防止缓存雪崩)
        $baseTTL = 3600;
        $randomOffset = random_int(0, 300);
        $finalTTL = $baseTTL + $randomOffset;
        Cache::put($cacheKey, $enabled ? 'true' : 'false', $finalTTL);

        return $enabled;
    }

    /**
     * 清除用户功能缓存
     *
     * @param int $userId 用户ID
     * @param string $featureKey 功能标识
     */
    public static function clearUserFeatureCache(int $userId, string $featureKey): void
    {
        $cacheKey = "user:{$userId}:feature:{$featureKey}";

        // 清除Redis缓存
        Cache::forget($cacheKey);
    }

    /**
     * 触发功能变更事件
     *
     * @param int $userId 用户ID
     * @param string $featureKey 功能标识
     * @param bool $oldValue 旧值
     * @param bool $newValue 新值
     * @param int $changedBy 变更人ID
     * @param string $reason 变更原因
     */
    public static function triggerFeatureChangeEvent(
        int $userId,
        string $featureKey,
        bool $oldValue,
        bool $newValue,
        int $changedBy,
        string $reason = ''
    ): void {
        FeatureChanged::dispatch(
            $userId,
            $featureKey,
            $oldValue,
            $newValue,
            $changedBy,
            $reason
        );
    }

    /**
     * 获取用户所有可用功能列表
     *
     * @param int $userId 用户ID
     * @return Collection
     */
    public static function getUserEnabledFeatures(int $userId): Collection
    {
        $allFeatures = Feature::all();
        $enabledFeatures = $allFeatures->filter(function ($feature) use ($userId) {
            return self::isEnabledWithCache($userId, $feature->key);
        });

        return $enabledFeatures;
    }

    /**
     * 设置用户个人开关
     *
     * @param int $userId 用户ID
     * @param string $featureKey 功能标识
     * @param bool $enabled 是否开启
     * @return UserFeatureSetting
     */
    public static function setUserFeatureSetting(int $userId, string $featureKey, bool $enabled): UserFeatureSetting
    {
        $feature = self::getFeatureByKey($featureKey);
        if (!$feature) {
            throw new FeatureNotFoundException($featureKey);
        }

        // 获取旧值
        $oldValue = self::isEnabled($userId, $featureKey);

        // 创建或更新用户设置
        $setting = UserFeatureSetting::updateOrCreate(
            [
                'user_id' => $userId,
                'feature_id' => $feature->id,
            ],
            [
                'is_enabled' => $enabled,
            ]
        );

        // 清除缓存
        self::clearUserFeatureCache($userId, $featureKey);

        // 触发变更事件
        self::triggerFeatureChangeEvent(
            $userId,
            $featureKey,
            $oldValue,
            $enabled,
            $userId,
            '用户个人设置变更'
        );

        return $setting;
    }

    /**
     * 添加用户到白名单
     *
     * @param int $userId 用户ID
     * @param string $featureKey 功能标识
     * @param string $reason 加入原因
     * @param int $operatorId 操作人ID(管理员或用户自己)
     * @return FeatureWhitelist
     */
    public static function addToWhitelist(int $userId, string $featureKey, string $reason = '', int $operatorId = 0): FeatureWhitelist
    {
        $feature = self::getFeatureByKey($featureKey);
        if (!$feature) {
            throw new FeatureNotFoundException($featureKey);
        }

        // 获取旧值
        $oldValue = self::isEnabled($userId, $featureKey);

        // 添加到白名单
        $whitelist = FeatureWhitelist::create([
            'feature_id' => $feature->id,
            'user_id' => $userId,
            'reason' => $reason,
        ]);

        // 清除缓存
        self::clearUserFeatureCache($userId, $featureKey);

        // 触发变更事件
        self::triggerFeatureChangeEvent(
            $userId,
            $featureKey,
            $oldValue,
            true,
            $operatorId,
            '添加到白名单: ' . $reason
        );

        return $whitelist;
    }

    /**
     * 添加用户到黑名单
     *
     * @param int $userId 用户ID
     * @param string $featureKey 功能标识
     * @param string $reason 加入原因
     * @param int $operatorId 操作人ID(管理员或用户自己)
     * @return FeatureBlacklist
     */
    public static function addToBlacklist(int $userId, string $featureKey, string $reason = '', int $operatorId = 0): FeatureBlacklist
    {
        $feature = self::getFeatureByKey($featureKey);
        if (!$feature) {
            throw new FeatureNotFoundException($featureKey);
        }

        // 获取旧值
        $oldValue = self::isEnabled($userId, $featureKey);

        // 添加到黑名单
        $blacklist = FeatureBlacklist::create([
            'feature_id' => $feature->id,
            'user_id' => $userId,
            'reason' => $reason,
        ]);

        // 清除缓存
        self::clearUserFeatureCache($userId, $featureKey);

        // 触发变更事件
        self::triggerFeatureChangeEvent(
            $userId,
            $featureKey,
            $oldValue,
            false,
            $operatorId,
            '添加到黑名单: ' . $reason
        );

        return $blacklist;
    }

    /**
     * 从白名单移除用户
     *
     * @param int $userId 用户ID
     * @param string $featureKey 功能标识
     * @param int $operatorId 操作人ID(管理员或用户自己)
     * @return bool
     */
    public static function removeFromWhitelist(int $userId, string $featureKey, int $operatorId = 0): bool
    {
        $feature = self::getFeatureByKey($featureKey);
        if (!$feature) {
            throw new FeatureNotFoundException($featureKey);
        }

        // 获取旧值
        $oldValue = self::isEnabled($userId, $featureKey);

        // 删除白名单记录
        $deleted = FeatureWhitelist::where('feature_id', $feature->id)
            ->where('user_id', $userId)
            ->delete();

        if ($deleted) {
            // 清除缓存
            self::clearUserFeatureCache($userId, $featureKey);

            // 获取新值
            $newValue = self::isEnabled($userId, $featureKey);

            // 触发变更事件
            self::triggerFeatureChangeEvent(
                $userId,
                $featureKey,
                $oldValue,
                $newValue,
                $operatorId,
                '从白名单移除'
            );
        }

        return $deleted > 0;
    }

    /**
     * 从黑名单移除用户
     *
     * @param int $userId 用户ID
     * @param string $featureKey 功能标识
     * @param int $operatorId 操作人ID(管理员或用户自己)
     * @return bool
     */
    public static function removeFromBlacklist(int $userId, string $featureKey, int $operatorId = 0): bool
    {
        $feature = self::getFeatureByKey($featureKey);
        if (!$feature) {
            throw new FeatureNotFoundException($featureKey);
        }

        // 获取旧值
        $oldValue = self::isEnabled($userId, $featureKey);

        // 删除黑名单记录
        $deleted = FeatureBlacklist::where('feature_id', $feature->id)
            ->where('user_id', $userId)
            ->delete();

        if ($deleted) {
            // 清除缓存
            self::clearUserFeatureCache($userId, $featureKey);

            // 获取新值
            $newValue = self::isEnabled($userId, $featureKey);

            // 触发变更事件
            self::triggerFeatureChangeEvent(
                $userId,
                $featureKey,
                $oldValue,
                $newValue,
                $operatorId,
                '从黑名单移除'
            );
        }

        return $deleted > 0;
    }
}