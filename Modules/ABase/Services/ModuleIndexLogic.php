<?php

namespace Modules\ABase\Services;

use Illuminate\Support\Collection;
use Nwidart\Modules\Facades\Module;

/**
 * 模块索引逻辑服务
 *
 * 提供模块信息收集、分析和格式化的静态方法
 */
class ModuleIndexLogic
{

    /**
     * 获取所有模块信息
     *
     * @return Collection
     */
    public static function getAllModules(): Collection
    {
        // 使用 Laravel Module facade 获取所有模块（包括禁用的）
        $modules = collect(app('modules')->all())->map(function ($module) {
            return self::analyzeModule($module);
        })->filter();

        // 添加模块分组信息
        $modules = $modules->map(function ($module) {
            $module['group_info'] = self::getModuleGroupInfo($module);
            return $module;
        });

        return $modules->sortBy('name');
    }

    /**
     * 获取按类型分组的模块
     *
     * @return Collection
     */
    public static function getModulesByType(): Collection
    {
        return self::getAllModules()->groupBy('type');
    }

    /**
     * 获取模块统计信息
     *
     * @return array
     */
    public static function getModulesStatistics(): array
    {
        $allModules = self::getAllModules();

        return [
            'total' => $allModules->count(),
            'by_type' => $allModules->groupBy('type')->map->count(),
            'enabled' => $allModules->where('enabled', true)->count(),
            'disabled' => $allModules->where('enabled', false)->count(),
            'has_admin' => $allModules->where('has_admin', true)->count(),
            'has_web' => $allModules->where('has_web', true)->count(),
        ];
    }

    /**
     * 搜索模块
     *
     * @param string $keyword
     * @return Collection
     */
    public static function searchModules(string $keyword): Collection
    {
        $keyword = strtolower($keyword);

        return self::getAllModules()->filter(function ($module) use ($keyword) {
            return str_contains(strtolower($module['name']), $keyword) ||
                str_contains(strtolower($module['description']), $keyword) ||
                str_contains(strtolower($module['alias'] ?? ''), $keyword);
        });
    }


    /**
     * 分析单个模块
     *
     * @param object $laravelModule
     * @return array|null
     */
    private static function analyzeModule($laravelModule): ?array
    {
        if (!$laravelModule) {
            return null;
        }

        $moduleName = $laravelModule->getName();
        $modulePath = $laravelModule->getPath();
        $moduleJsonPath = $modulePath . '/module.json';

        $moduleData = [];
        if (file_exists($moduleJsonPath)) {
            $moduleData = json_decode(file_get_contents($moduleJsonPath), true) ?? [];
        }
        $res =  [
            'name' => $moduleName,
            'alias' => $moduleData['alias'] ?? null,
            'description' => $moduleData['description'] ?? '无描述',
            'keywords' => $moduleData['keywords'] ?? [],
            'version' => $moduleData['version'] ?? '1.0.0',
            'priority' => $moduleData['priority'] ?? 0,
            'type' => '模块', // 统一类型，不再基于目录
            'directory' => '', // 不再需要目录信息
            'module_type' => $moduleData['module_type'] ?? 'base',
            'enabled' => $laravelModule->isEnabled(),
            'dependencies' => $moduleData['requires'] ?? ($moduleData['module_requires'] ?? []),
            'has_admin' => self::checkHasAdmin($modulePath),
            'has_web' => self::checkHasWeb($modulePath),
            'has_api' => self::checkHasApi($modulePath),
            'providers' => $moduleData['providers'] ?? [],
            'files' => $moduleData['files'] ?? [],
            'path' => $modulePath,
            'relative_path' => str_replace(base_path(), '', $modulePath),
            'last_modified' => file_exists($moduleJsonPath) ? filemtime($moduleJsonPath) : 0,
        ];
        // dump($moduleData,$res);

        return $res;
    }

    /**
     * 检查模块是否有后台管理
     *
     * @param string $modulePath
     * @return bool
     */
    private static function checkHasAdmin(string $modulePath): bool
    {
        return is_dir($modulePath . '/Admin') ||
            is_dir($modulePath . '/DcatAdmin') ||
            file_exists($modulePath . '/routes/admin.php');
    }

    /**
     * 检查模块是否有Web前台
     *
     * @param string $modulePath
     * @return bool
     */
    private static function checkHasWeb(string $modulePath): bool
    {
        return is_dir($modulePath . '/resources/views') ||
            file_exists($modulePath . '/routes/web.php');
    }

    /**
     * 检查模块是否有API接口
     *
     * @param string $modulePath
     * @return bool
     */
    private static function checkHasApi(string $modulePath): bool
    {
        return file_exists($modulePath . '/routes/api.php') ||
            is_dir($modulePath . '/Controllers');
    }

    /**
     * 获取模块分组信息
     *
     * @param array $module
     * @return array
     */
    private static function getModuleGroupInfo(array $module): array
    {
        $moduleName = $module['name'];
        $moduleType = $module['module_type'] ?? 'base';

        // 根据module_type判断是否为主模块
        $isMainModule = $moduleType === 'base';

        // 如果是主模块，查找其附属模块
        if ($isMainModule) {
            $subModules = self::findSubModules($moduleName);
            return [
                'type' => 'main',
                'sub_modules' => $subModules,
                'has_sub_modules' => !empty($subModules)
            ];
        }

        // 如果是附属模块，识别其主模块
        $mainModuleName = self::findMainModule($moduleName);
        return [
            'type' => 'sub',
            'main_module' => $mainModuleName
        ];
    }

    /**
     * 查找主模块的附属模块
     *
     * @param string $mainModuleName
     * @return array
     */
    private static function findSubModules(string $mainModuleName): array
    {
        $allModules = self::getAllRawModules();
        $subModules = [];

        foreach ($allModules as $module) {
            // 检查模块是否依赖当前主模块
            if (in_array($mainModuleName, $module['dependencies'])) {
                $subModules[] = [
                    'name' => $module['name'],
                    'type' => $module['module_type'],
                    'module' => $module
                ];
            }
        }

        return $subModules;
    }

    /**
     * 查找附属模块对应的主模块
     *
     * @param string $subModuleName
     * @return string|null
     */
    private static function findMainModule(string $subModuleName): ?string
    {
        $allModules = self::getAllRawModules();
        $subModule = $allModules->firstWhere('name', $subModuleName);

        if ($subModule && !empty($subModule['dependencies'])) {
            // 返回第一个依赖的主模块
            return $subModule['dependencies'][0];
        }

        return null;
    }


    /**
     * 获取原始模块列表（不包含分组信息，避免递归）
     *
     * @return Collection
     */
    private static function getAllRawModules(): Collection
    {
        // 使用 Laravel Module facade 获取所有模块（包括禁用的）
        return collect(app('modules')->all())->map(function ($module) {
            return self::analyzeModule($module);
        })->filter()->sortBy('name');
    }

    /**
     * 获取按业务分组的模块
     *
     * @return Collection
     */
    public static function getModulesByBusiness(): Collection
    {
        $allModules = self::getAllModules();
        $businessGroups = collect();

        // 只处理主模块
        $mainModules = $allModules->filter(function ($module) {
            return $module['group_info']['type'] === 'main';
        });

        foreach ($mainModules as $module) {
            $businessName = $module['name'];
            $businessGroups->put($businessName, [
                'main_module' => $module,
                'sub_modules' => $module['group_info']['sub_modules'],
                'all_modules' => collect([$module])
                    ->concat($module['group_info']['sub_modules']->pluck('module'))
            ]);
        }

        // 处理没有找到主模块的独立模块
        $orphanModules = $allModules->filter(function ($module) {
            return $module['group_info']['type'] === 'sub' && !$module['group_info']['main_module'];
        });

        foreach ($orphanModules as $module) {
            $businessName = $module['name'] . ' (独立)';
            $businessGroups->put($businessName, [
                'main_module' => null,
                'sub_modules' => [],
                'all_modules' => collect([$module])
            ]);
        }

        return $businessGroups;
    }
}
