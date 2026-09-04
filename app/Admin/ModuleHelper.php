<?php

namespace App\Admin;

use Dcat\Admin\Admin;
use Modules\ABase\Support\Trace;

/**
 * 模块化助手类
 * 提供商户后台模块的通用功能和方法
 */
class ModuleHelper
{
    /**
     * 加载所有已启用模块的 admin_menu.php 配置并添加到后台菜单
     *
     * @deprecated 已废弃，改用 MenuSyncService::syncAll() 进行菜单同步
     *             请使用 php artisan admin:sync-menu 命令或访问后台菜单同步页面
     *
     * @param string $name 配置文件名称（默认为 admin_menu）
     * @return array|null 同步结果（如果执行同步）
     */
    public static function loadModuleAdminMenus($name)
    {
        // 标记为废弃，建议使用新的菜单同步服务
        \Illuminate\Support\Facades\Log::warning(
            'ModuleHelper::loadModuleAdminMenus() 已废弃，请使用 MenuSyncService::syncAll()'
        );

        // 改为调用菜单同步服务（仅在明确调用时执行）
        if (app()->bound(\Modules\DcatAdmin\Services\MenuSyncService::class)) {
            return app(\Modules\DcatAdmin\Services\MenuSyncService::class)->syncAll();
        }

        return null;
    }

    /**
     * 获取所有已启用模块的菜单配置（供服务层使用）
     *
     * @return array 模块配置数组 [模块名 => 菜单配置数组]
     */
    public static function getModuleMenuConfigs(): array
    {
        $configs = [];

        // 检查modules服务是否可用
        if (!app()->bound('modules')) {
            return $configs;
        }

        // 获取已启用的模块
        $modules = app('modules')->getByStatus(1);

        foreach ($modules as $moduleName => $module) {
            $path = $module->getPath();

            // 检查配置文件是否存在
            $configFile = $path . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'admin_menu.php';

            if (!is_file($configFile)) {
                continue;
            }

            // 读取配置
            $configKey = 'module_' . $moduleName . '::admin_menu';

            if (config()->has($configKey)) {
                $configs[$moduleName] = config($configKey, []);
            }
        }

        return $configs;
    }
}
