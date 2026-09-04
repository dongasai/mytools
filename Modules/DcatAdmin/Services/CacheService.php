<?php

namespace Modules\DcatAdmin\Services;

use Modules\Admin\Enums\CACHE_TYPE;
use DLaravel\Helper\Logger;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * 缓存管理服务
 */
class CacheService
{
    /**
     * 清理所有缓存
     */
    public function clearAll(): array
    {
        $results = [];

        try {
            // 清理应用缓存
            Cache::flush();
            $results['application'] = ['status' => 'success', 'message' => '应用缓存清理成功'];

            // 清理配置缓存
            Artisan::call('config:clear');
            $results['config'] = ['status' => 'success', 'message' => '配置缓存清理成功'];

            // 清理路由缓存
            Artisan::call('route:clear');
            $results['route'] = ['status' => 'success', 'message' => '路由缓存清理成功'];

            // 清理视图缓存
            Artisan::call('view:clear');
            $results['view'] = ['status' => 'success', 'message' => '视图缓存清理成功'];

            // 清理事件缓存
            Artisan::call('event:clear');
            $results['event'] = ['status' => 'success', 'message' => '事件缓存清理成功'];

            Log::info('Admin: 所有缓存清理完成');

        } catch (\Exception $e) {
            $results['error'] = ['status' => 'error', 'message' => $e->getMessage()];
            Logger::error('Admin: 缓存清理失败', ['error' => $e->getMessage()]);
        }

        return $results;
    }

    /**
     * 按类型清理缓存
     */
    public function clearByType(CACHE_TYPE $type): array
    {
        try {
            switch ($type) {
                case CACHE_TYPE::CONFIG:
                    Artisan::call('config:clear');

                    return ['status' => 'success', 'message' => '配置缓存清理成功'];

                case CACHE_TYPE::ROUTE:
                    Artisan::call('route:clear');

                    return ['status' => 'success', 'message' => '路由缓存清理成功'];

                case CACHE_TYPE::VIEW:
                    Artisan::call('view:clear');

                    return ['status' => 'success', 'message' => '视图缓存清理成功'];

                case CACHE_TYPE::EVENT:
                    Artisan::call('event:clear');

                    return ['status' => 'success', 'message' => '事件缓存清理成功'];

                case CACHE_TYPE::APPLICATION:
                    Cache::flush();

                    return ['status' => 'success', 'message' => '应用缓存清理成功'];

                default:
                    // 按标签清理
                    $tags = $type->getTags();
                    if (! empty($tags)) {
                        Cache::tags($tags)->flush();

                        return ['status' => 'success', 'message' => "{$type->getLabel()}清理成功"];
                    }

                    return ['status' => 'error', 'message' => '不支持的缓存类型'];
            }
        } catch (\Exception $e) {
            Logger::error("Admin: {$type->getLabel()}清理失败", ['error' => $e->getMessage()]);

            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * 获取缓存状态
     */
    public function getStatus(): array
    {
        $status = [];

        foreach (CACHE_TYPE::cases() as $type) {
            $status[$type->value] = $this->getCacheTypeStatus($type);
        }

        return $status;
    }

    /**
     * 获取特定类型缓存的状态
     */
    protected function getCacheTypeStatus(CACHE_TYPE $type): array
    {
        try {
            $testKey = "admin.cache.test.{$type->value}";
            $testValue = 'test_'.time();

            // 测试写入
            Cache::tags($type->getTags())->put($testKey, $testValue, 60);

            // 测试读取
            $retrieved = Cache::tags($type->getTags())->get($testKey);

            // 清理测试数据
            Cache::tags($type->getTags())->forget($testKey);

            $isWorking = $retrieved === $testValue;

            return [
                'type' => $type->value,
                'label' => $type->getLabel(),
                'status' => $isWorking ? 'healthy' : 'error',
                'description' => $type->getDescription(),
                'ttl' => $type->getDefaultTtl(),
                'tags' => $type->getTags(),
                'priority' => $type->getPriority(),
                'is_critical' => $type->isCritical(),
                'safe_to_clear' => $type->isSafeToClear(),
            ];
        } catch (\Exception $e) {
            return [
                'type' => $type->value,
                'label' => $type->getLabel(),
                'status' => 'error',
                'error' => $e->getMessage(),
                'description' => $type->getDescription(),
            ];
        }
    }

    /**
     * 获取缓存统计信息
     */
    public function getStatistics(): array
    {
        try {
            return [
                'driver' => config('cache.default'),
                'stores' => config('cache.stores'),
                'total_types' => count(CACHE_TYPE::cases()),
                'critical_types' => count(array_filter(CACHE_TYPE::cases(), fn ($type) => $type->isCritical())),
                'safe_to_clear' => count(array_filter(CACHE_TYPE::cases(), fn ($type) => $type->isSafeToClear())),
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * 预热缓存
     */
    public function warmup(array $types = []): array
    {
        $results = [];

        if (empty($types)) {
            $types = array_map(fn ($type) => $type->value, CACHE_TYPE::cases());
        }

        foreach ($types as $typeValue) {
            try {
                $type = CACHE_TYPE::from($typeValue);
                $result = $this->warmupCacheType($type);
                $results[$type->value] = $result;
            } catch (\Exception $e) {
                $results[$typeValue] = [
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * 预热特定类型的缓存
     */
    protected function warmupCacheType(CACHE_TYPE $type): array
    {
        try {
            switch ($type) {
                case CACHE_TYPE::CONFIG:
                    // 预热配置缓存
                    Artisan::call('config:cache');

                    return ['status' => 'success', 'message' => '配置缓存预热成功'];

                case CACHE_TYPE::ROUTE:
                    // 预热路由缓存
                    Artisan::call('route:cache');

                    return ['status' => 'success', 'message' => '路由缓存预热成功'];

                case CACHE_TYPE::VIEW:
                    // 预热视图缓存
                    Artisan::call('view:cache');

                    return ['status' => 'success', 'message' => '视图缓存预热成功'];

                case CACHE_TYPE::EVENT:
                    // 预热事件缓存
                    Artisan::call('event:cache');

                    return ['status' => 'success', 'message' => '事件缓存预热成功'];

                default:
                    return ['status' => 'skipped', 'message' => '该类型缓存不支持预热'];
            }
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * 获取缓存大小信息
     */
    public function getSizeInfo(): array
    {
        try {
            // 这里可以根据不同的缓存驱动实现大小统计
            // 目前返回基本信息
            return [
                'driver' => config('cache.default'),
                'message' => '缓存大小统计功能待实现',
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }
}
