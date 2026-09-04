<?php

namespace Modules\AFile\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\AFile\Models\FileStorageConfig;
use Modules\AFile\Models\FileStorageConfigHistory;
use Modules\Application\Services\SystemLogService;

/**
 * 存储配置服务类
 *
 * 提供存储配置的业务逻辑处理，包括配置的读取、更新和缓存管理
 */
class StorageConfigService
{
    /**
     * 缓存前缀
     */
    const CACHE_PREFIX = 'file_storage_config:';

    /**
     * 缓存过期时间（秒）
     */
    const CACHE_TTL = 3600; // 1小时

    /**
     * 获取默认存储磁盘配置
     *
     * @return FileStorageConfig|null 存储配置模型
     */
    public static function getDefaultDisk(): ?FileStorageConfig
    {
        return Cache::remember(self::CACHE_PREFIX.'default:'.app()->environment(), self::CACHE_TTL, function () {
            return FileStorageConfig::where('is_default', true)
                ->where('status', true)
                ->where('env', app()->environment())
                ->first();
        });
    }

    /**
     * 获取临时存储磁盘配置
     *
     * @return FileStorageConfig|null 存储配置模型
     */
    public static function getTempDisk(): ?FileStorageConfig
    {
        return Cache::remember(self::CACHE_PREFIX.'temp:'.app()->environment(), self::CACHE_TTL, function () {
            return FileStorageConfig::where('is_temp', true)
                ->where('status', true)
                ->where('env', app()->environment())
                ->first();
        });
    }

    /**
     * 获取指定名称的存储磁盘配置
     *
     * @param  string  $name  存储磁盘名称
     * @return FileStorageConfig|null 存储配置模型
     */
    public static function getDisk(string $name): ?FileStorageConfig
    {
        return Cache::remember(self::CACHE_PREFIX.'name:'.$name.':'.app()->environment(), self::CACHE_TTL, function () use ($name) {
            return FileStorageConfig::where('name', $name)
                ->where('status', true)
                ->where('env', app()->environment())
                ->first();
        });
    }

    /**
     * 获取所有启用的存储磁盘配置
     *
     * @param  string|null  $env  环境名称，为null时获取当前环境
     * @return \Illuminate\Database\Eloquent\Collection 存储配置集合
     */
    public static function getAllDisks(?string $env = null): \Illuminate\Database\Eloquent\Collection
    {
        $env = $env ?: app()->environment();

        return Cache::remember(self::CACHE_PREFIX.'all:'.$env, self::CACHE_TTL, function () use ($env) {
            return FileStorageConfig::where('status', true)
                ->where('env', $env)
                ->get();
        });
    }

    /**
     * 创建存储磁盘配置
     *
     * @param  string  $name  存储磁盘名称
     * @param  string  $driver  存储驱动
     * @param  array  $config  配置值
     * @param  string  $description  配置描述
     * @param  bool  $isDefault  是否默认存储
     * @param  bool  $isTemp  是否用于临时存储
     * @param  string  $env  环境名称
     * @param  int  $createdBy  创建人ID
     * @return FileStorageConfig 存储配置模型
     */
    public static function createDisk(string $name, string $driver, array $config, string $description = '', bool $isDefault = false, bool $isTemp = false, string $env = 'production', int $createdBy = 0): FileStorageConfig
    {
        // 如果设置为默认存储，则将其他配置的默认标志设为false
        if ($isDefault) {
            self::resetDefaultFlag($env);
        }

        // 如果设置为临时存储，则将其他配置的临时标志设为false
        if ($isTemp) {
            self::resetTempFlag($env);
        }

        $storageConfig = new FileStorageConfig;
        $storageConfig->name = $name;
        $storageConfig->driver = $driver;
        $storageConfig->config = $config;
        $storageConfig->description = $description;
        $storageConfig->is_default = $isDefault;
        $storageConfig->is_temp = $isTemp;
        $storageConfig->env = $env;
        $storageConfig->status = true;
        $storageConfig->created_by = $createdBy;
        $storageConfig->updated_by = $createdBy;
        $storageConfig->save();

        // 清除缓存
        self::clearCache();

        return $storageConfig;
    }

    /**
     * 更新存储磁盘配置
     *
     * @param  int  $id  存储配置ID
     * @param  string  $driver  存储驱动
     * @param  array  $config  配置值
     * @param  string  $description  配置描述
     * @param  bool  $isDefault  是否默认存储
     * @param  bool  $isTemp  是否用于临时存储
     * @param  int  $status  状态
     * @param  int  $updatedBy  更新人ID
     * @param  string  $reason  变更原因
     * @return FileStorageConfig 存储配置模型
     */
    public static function updateDisk(int $id, string $driver, array $config, string $description = '', bool $isDefault = false, bool $isTemp = false, int $status = 1, int $updatedBy = 0, string $reason = ''): FileStorageConfig
    {
        $storageConfig = FileStorageConfig::findOrFail($id);
        $oldData = $storageConfig->toArray();

        // 如果设置为默认存储，则将其他配置的默认标志设为false
        if ($isDefault && ! $storageConfig->is_default) {
            self::resetDefaultFlag($storageConfig->env);
        }

        // 如果设置为临时存储，则将其他配置的临时标志设为false
        if ($isTemp && ! $storageConfig->is_temp) {
            self::resetTempFlag($storageConfig->env);
        }

        $storageConfig->driver = $driver;
        $storageConfig->config = $config;
        $storageConfig->description = $description;
        $storageConfig->is_default = $isDefault;
        $storageConfig->is_temp = $isTemp;
        $storageConfig->status = (bool) $status;
        $storageConfig->updated_by = $updatedBy;
        $storageConfig->save();

        // 记录变更历史
        self::recordHistory($id, $oldData, $storageConfig->toArray(), $updatedBy, $reason);

        // 清除缓存
        self::clearCache();

        return $storageConfig;
    }

    /**
     * 删除存储磁盘配置
     *
     * @param  int  $id  存储配置ID
     * @param  int  $deletedBy  删除人ID
     * @param  string  $reason  删除原因
     * @return bool 是否成功
     */
    public static function deleteDisk(int $id, int $deletedBy = 0, string $reason = ''): bool
    {
        $storageConfig = FileStorageConfig::findOrFail($id);
        $oldData = $storageConfig->toArray();

        // 记录删除历史
        self::recordHistory($id, $oldData, [], $deletedBy, $reason ?: '删除配置');

        $result = $storageConfig->delete();

        // 清除缓存
        self::clearCache();

        return $result;
    }

    /**
     * 设置默认存储磁盘
     *
     * @param  int  $id  存储配置ID
     * @param  int  $updatedBy  更新人ID
     * @return bool 是否成功
     */
    public static function setDefaultDisk(int $id, int $updatedBy = 0): bool
    {
        $storageConfig = FileStorageConfig::findOrFail($id);
        $oldData = $storageConfig->toArray();

        // 重置当前环境中的所有默认标志
        self::resetDefaultFlag($storageConfig->env);

        // 设置当前配置为默认
        $storageConfig->is_default = true;
        $storageConfig->updated_by = $updatedBy;
        $result = $storageConfig->save();

        // 记录变更历史
        self::recordHistory($id, $oldData, $storageConfig->toArray(), $updatedBy, '设置为默认存储磁盘');

        // 清除缓存
        self::clearCache();

        return $result;
    }

    /**
     * 设置临时存储磁盘
     *
     * @param  int  $id  存储配置ID
     * @param  int  $updatedBy  更新人ID
     * @return bool 是否成功
     */
    public static function setTempDisk(int $id, int $updatedBy = 0): bool
    {
        $storageConfig = FileStorageConfig::findOrFail($id);
        $oldData = $storageConfig->toArray();

        // 重置当前环境中的所有临时标志
        self::resetTempFlag($storageConfig->env);

        // 设置当前配置为临时存储
        $storageConfig->is_temp = true;
        $storageConfig->updated_by = $updatedBy;
        $result = $storageConfig->save();

        // 记录变更历史
        self::recordHistory($id, $oldData, $storageConfig->toArray(), $updatedBy, '设置为临时存储磁盘');

        // 清除缓存
        self::clearCache();

        return $result;
    }

    /**
     * 获取存储磁盘配置变更历史
     *
     * @param  int  $configId  存储配置ID
     * @return \Illuminate\Database\Eloquent\Collection 配置历史集合
     */
    public static function getHistory(int $configId): \Illuminate\Database\Eloquent\Collection
    {
        return FileStorageConfigHistory::where('config_id', $configId)
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * 清除存储配置缓存
     *
     * @return void
     */
    public static function clearCache(): void
    {
        $cacheKeys = [
            self::CACHE_PREFIX.'default:'.app()->environment(),
            self::CACHE_PREFIX.'temp:'.app()->environment(),
            self::CACHE_PREFIX.'all:'.app()->environment(),
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }

        // 清除所有名称缓存
        $disks = FileStorageConfig::where('env', app()->environment())->get();
        foreach ($disks as $disk) {
            Cache::forget(self::CACHE_PREFIX.'name:'.$disk->name.':'.app()->environment());
        }

        Log::info('存储配置缓存已清除');
    }

    /**
     * 重置默认存储标志
     *
     * @param  string  $env  环境名称
     * @return void
     */
    protected static function resetDefaultFlag(string $env): void
    {
        FileStorageConfig::where('env', $env)
            ->where('is_default', true)
            ->update(['is_default' => false]);
    }

    /**
     * 重置临时存储标志
     *
     * @param  string  $env  环境名称
     * @return void
     */
    protected static function resetTempFlag(string $env): void
    {
        FileStorageConfig::where('env', $env)
            ->where('is_temp', true)
            ->update(['is_temp' => false]);
    }

    /**
     * 记录存储配置变更历史
     *
     * @param  int  $configId  存储配置ID
     * @param  array  $oldData  旧数据
     * @param  array  $newData  新数据
     * @param  int  $changedBy  变更人ID
     * @param  string  $reason  变更原因
     * @return FileStorageConfigHistory 配置历史模型
     */
    protected static function recordHistory(int $configId, array $oldData, array $newData, int $changedBy, string $reason = ''): FileStorageConfigHistory
    {
        return FileStorageConfigHistory::create([
            'config_id' => $configId,
            'old_driver' => $oldData['driver'] ?? null,
            'new_driver' => $newData['driver'] ?? null,
            'old_config' => isset($oldData['config']) ? $oldData['config'] : null,
            'new_config' => isset($newData['config']) ? $newData['config'] : null,
            'old_status' => $oldData['status'] ?? null,
            'new_status' => $newData['status'] ?? null,
            'changed_by' => $changedBy,
            'change_reason' => $reason,
        ]);
    }

    /**
     * 通过配置ID测试存储连接
     *
     * @param  int  $id  存储配置ID
     * @return array 测试结果，包含success和message字段
     */
    public static function testConnectionById(int $id): array
    {
        $storage = FileStorageConfig::find($id);
        if (!$storage) {
            return [
                'success' => false,
                'message' => '存储配置不存在',
            ];
        }

        // 检查配置状态
        if (!$storage->status) {
            return [
                'success' => false,
                'message' => '存储配置已禁用，请先启用配置',
            ];
        }

        return self::testConnection($storage->driver, $storage->config);
    }

    /**
     * 测试存储配置连接
     *
     * @param  string  $driver  存储驱动
     * @param  array  $config  配置值
     * @return array 测试结果，包含success和message字段
     */
    public static function testConnection(string $driver, array $config): array
    {
        try {
            // 创建临时磁盘配置
            $diskName = 'temp_test_'.time();
            $fullConfig = array_merge(['driver' => $driver], $config, ['throw' => true]);
            config(["filesystems.disks.{$diskName}" => $fullConfig]);

            // 测试连接
            $disk = \Storage::disk($diskName);
            $testFile = 'test_'.time().'.txt';
            $disk->put($testFile, 'Testing connection at '.now());
            $disk->get($testFile); // 验证能够读取
            $disk->delete($testFile);

            return [
                'success' => true,
                'message' => '连接成功！测试文件已成功创建和删除。',
            ];
        } catch (\Exception $e) {
            SystemLogService::exception('afile', $e, [
                'operation' => 'testConnection',
                'driver' => $driver,
                'config' => $config,
            ]);

            return [
                'success' => false,
                'message' => '连接失败：'.$e->getMessage(),
            ];
        }
    }

    /**
     * 注册存储配置到Laravel文件系统
     *
     * @return void
     */
    public static function registerStorageConfigs(): void
    {
        $disks = self::getAllDisks();

        foreach ($disks as $disk) {
            // 确保 config 是数组（防御性检查）
            $diskConfig = is_array($disk->config) ? $disk->config : [];
            $config = array_merge(['driver' => $disk->driver], $diskConfig);
            config(["filesystems.disks.{$disk->name}" => $config]);
        }

        // 设置默认磁盘
        $defaultDisk = self::getDefaultDisk();
        if ($defaultDisk) {
            config(['filesystems.default' => $defaultDisk->name]);
        }
    }

    /**
     * 同步 filesystems.php 配置到数据库
     *
     * 从 config/filesystems.php 读取磁盘配置，同步到 file_storage_configs 表
     *
     * @param  string|null  $env  环境名称，默认为当前环境
     * @param  int  $createdBy  创建人ID
     * @return array 同步结果，包含 created, updated, skipped 统计
     */
    public static function syncFromFilesystems(?string $env = null, int $createdBy = 0): array
    {
        $env = $env ?: app()->environment();
        $filesystems = config('filesystems.disks', []);

        $stats = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
        ];

        foreach ($filesystems as $diskName => $diskConfig) {
            // 跳过没有 driver 的配置
            if (!isset($diskConfig['driver'])) {
                $stats['skipped']++;
                continue;
            }

            // 提取配置（排除 driver 字段）
            $driver = $diskConfig['driver'];
            $config = array_filter($diskConfig, function ($key) {
                return $key !== 'driver';
            }, ARRAY_FILTER_USE_KEY);

            // 检查是否已存在
            $existing = FileStorageConfig::where('name', $diskName)
                ->where('env', $env)
                ->first();

            if ($existing) {
                // 更新现有配置
                $existing->driver = $driver;
                $existing->config = $config;
                $existing->updated_by = $createdBy;
                $existing->save();
                $stats['updated']++;
            } else {
                // 创建新配置
                $isDefault = $diskName === config('filesystems.default');
                $description = self::getDiskDescription($diskName, $driver);

                self::createDisk(
                    $diskName,
                    $driver,
                    $config,
                    $description,
                    $isDefault,
                    false, // is_temp
                    $env,
                    $createdBy
                );
                $stats['created']++;
            }
        }

        // 清除缓存
        self::clearCache();

        return $stats;
    }

    /**
     * 获取磁盘描述
     *
     * @param  string  $diskName  磁盘名称
     * @param  string  $driver  驱动类型
     * @return string 描述文本
     */
    protected static function getDiskDescription(string $diskName, string $driver): string
    {
        $descriptions = [
            'local' => '本地文件存储，文件保存在 storage/app/private 目录',
            'public' => '本地公共文件存储，文件保存在 storage/app/public 目录',
            's3' => 'Amazon S3 云存储',
            'oss' => '阿里云 OSS 对象存储',
            'ftp' => 'FTP 文件传输协议存储',
            'sftp' => 'SFTP 安全文件传输协议存储',
        ];

        return $descriptions[$diskName] ?? $descriptions[$driver] ?? "{$driver} 存储驱动";
    }
}
