<?php

namespace Modules\AFile\Logics;

use Modules\AFile\Services\StorageConfigService;

/**
 * 存储配置类
 *
 * 提供存储相关的配置
 */
class StorageConfig
{
    /**
     * 获取默认存储磁盘
     *
     * @return string 存储磁盘名称
     */
    public static function getStorage()
    {
        // 从数据库获取默认存储配置
        $storageConfigService = new StorageConfigService;
        $defaultDisk = $storageConfigService->getDefaultDisk();

        // 如果数据库中有默认配置，使用数据库配置
        if ($defaultDisk) {
            return $defaultDisk->name;
        }

        // 否则使用环境变量配置（作为备用）
        return env('FILESYSTEM_DISK', 'local');
    }

    /**
     * 获取临时存储磁盘
     *
     * 从数据库临时盘配置（is_temp）读取，未配置时回退默认存储
     *
     * @return string 临时存储磁盘名称
     */
    public static function getTempStorage()
    {
        $tempConfig = StorageConfigService::getTempDisk();

        return $tempConfig ? $tempConfig->name : self::getStorage();
    }
}
