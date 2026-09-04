<?php

namespace Modules\DcatAdmin\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Modules\DcatAdmin\Models\AdminMenu;

/**
 * 菜单同步服务
 *
 * 菜单同步是类似 Seeder 的初始化机制，用于补充缺失的菜单。
 * 只负责插入新菜单，不会覆盖已有的菜单（允许后期手动修改）。
 */
class MenuSyncService
{
    /**
     * 同步所有模块菜单
     *
     * @param bool $deleteOrphan 是否删除孤儿菜单
     * @return array 同步结果报告
     */
    public function syncAll(bool $deleteOrphan = false): array
    {
        $startTime = microtime(true);

        $report = [
            'modules_scanned' => 0,
            'total_menu_items' => 0,
            'inserted' => 0,
            'skipped' => 0,
            'errors' => 0,
            'orphans_found' => 0,
            'orphans_deleted' => 0,
            'details' => [],
            'errors_details' => [],
            'duration' => 0,
        ];

        // 获取所有已启用模块的菜单配置（包含核心菜单）
        $moduleConfigs = $this->getModuleMenuConfigs();

        $report['modules_scanned'] = count($moduleConfigs);

        // 检查URI冲突
        $conflicts = $this->checkUriConflicts($moduleConfigs);
        if (!empty($conflicts)) {
            $report['errors'] += count($conflicts);
            $report['errors_details']['uri_conflicts'] = $conflicts;
            Log::warning('菜单同步发现URI冲突', $conflicts);
        }

        // 同步每个模块的菜单
        foreach ($moduleConfigs as $moduleName => $menuItems) {
            $moduleResult = $this->syncModule($moduleName);
            $report['total_menu_items'] += $moduleResult['total_items'];
            $report['inserted'] += $moduleResult['inserted'];
            $report['skipped'] += $moduleResult['skipped'];
            $report['errors'] += $moduleResult['errors'];
            $report['details'][$moduleName] = $moduleResult;
        }

        // 检查孤儿菜单
        if ($deleteOrphan) {
            $orphans = $this->findOrphanMenus($moduleConfigs);
            $report['orphans_found'] = count($orphans);

            if (count($orphans) > 0) {
                $deleted = $this->deleteOrphanMenus($orphans);
                $report['orphans_deleted'] = $deleted;
                Log::info('删除孤儿菜单', ['count' => $deleted, 'ids' => $orphans]);
            }
        }

        // 清除菜单缓存
        $this->clearMenuCache();

        $report['duration'] = round(microtime(true) - $startTime, 2);

        Log::info('菜单同步完成', $report);

        return $report;
    }

    /**
     * 同步指定模块的菜单
     *
     * @param string $moduleName 模块名称
     * @return array 同步结果
     */
    public function syncModule(string $moduleName): array
    {
        $result = [
            'module' => $moduleName,
            'modules_scanned' => 1, // 单模块同步，扫描数量为1
            'total_menu_items' => 0,
            'inserted' => 0,
            'updated' => 0,  // 菜单同步只插入不更新，此字段始终为0
            'skipped' => 0,
            'errors' => 0,
            'orphans_found' => 0,
            'orphans_deleted' => 0,
            'details' => [],
            'errors_details' => [],
            'duration' => 0,
            // 兼容单模块的内部字段
            'total_items' => 0,
            'items' => [],
        ];

        $startTime = microtime(true);

        // 获取模块菜单配置：优先从config获取，不可用时直接读取文件
        $configKey = 'module_' . strtolower($moduleName) . '::admin_menu';
        $menuItems = config($configKey);

        if (empty($menuItems)) {
            // config未注册，尝试直接读取文件
            $module = app('modules')->find($moduleName);
            if ($module) {
                $configFile = $module->getPath() . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'admin_menu.php';
                if (is_file($configFile)) {
                    $menuItems = require $configFile;
                }
            }
        }

        if (empty($menuItems)) {
            $result['errors']++;
            $result['items'][] = [
                'status' => 'error',
                'menu_id' => 0,
                'title' => '',
                'uri' => '',
                'message' => '模块配置为空或不存在',
                'config_key' => $configKey,
            ];
            $result['errors_details']['config_empty'] = [
                'module' => $moduleName,
                'config_key' => $configKey,
            ];
            return $result;
        }

        $result['total_items'] = count($menuItems);
        $result['total_menu_items'] = count($menuItems);

        // 同步每个菜单项
        foreach ($menuItems as $menuItem) {
            $itemResult = $this->syncMenuItem($menuItem);
            // 修正 status 键名：'error' 对应 'errors' 统计
            $statusKey = $itemResult['status'] === 'error' ? 'errors' : $itemResult['status'];
            $result[$statusKey]++;
            $result['items'][] = $itemResult;
        }

        $result['duration'] = round(microtime(true) - $startTime, 2);
        $result['details'][$moduleName] = [
            'module' => $moduleName,
            'total_items' => $result['total_items'],
            'inserted' => $result['inserted'],
            'skipped' => $result['skipped'],
            'errors' => $result['errors'],
            'items' => $result['items'],
        ];

        // 清除菜单缓存
        $this->clearMenuCache();

        Log::info("模块 {$moduleName} 菜单同步完成", $result);

        return $result;
    }

    /**
     * 预览同步结果（不执行实际操作）
     *
     * @return array 预览报告
     */
    public function previewSync(): array
    {
        $preview = [
            'modules' => [],
            'database_menus' => [],
            'config_menus' => [],
            'differences' => [
                'to_insert' => [],
                'to_update' => [],
                'orphans' => [],
                'conflicts' => [],
            ],
        ];

        // 获取数据库现有菜单并建立索引（一次性加载，避免重复查询）
        $dbMenus = AdminMenu::all()->toArray();
        $dbMenuById = [];  // 按ID索引
        $dbMenuByUri = []; // 按URI索引

        foreach ($dbMenus as $menu) {
            $dbMenuById[$menu['id']] = $menu;
            if (!empty($menu['uri'])) {
                $dbMenuByUri[$menu['uri']] = $menu;
            }
        }

        $preview['database_menus'] = [
            'total' => count($dbMenus),
            'parents' => count(array_filter($dbMenus, fn($m) => $m['parent_id'] == 0)),
            'children' => count(array_filter($dbMenus, fn($m) => $m['parent_id'] != 0)),
        ];

        // 获取配置文件菜单
        $moduleConfigs = $this->getModuleMenuConfigs();

        foreach ($moduleConfigs as $moduleName => $menuItems) {
            $preview['modules'][$moduleName] = [
                'menu_count' => count($menuItems),
                'config_key' => 'module_' . $moduleName . '::admin_menu',
            ];

            foreach ($menuItems as $menuItem) {
                // 检查菜单项是否有 id 字段
                if (!isset($menuItem['id'])) {
                    Log::warning("模块 {$moduleName} 的菜单项缺少 id 字段", [
                        'title' => $menuItem['title'] ?? $menuItem['label'] ?? '未知',
                        'uri' => $menuItem['uri'] ?? '',
                    ]);
                    $preview['differences']['conflicts'][] = [
                        'uri' => $menuItem['uri'] ?? '',
                        'modules' => [$moduleName],
                        'menu_ids' => ['未知'],
                        'message' => "模块 {$moduleName} 的菜单项缺少必需的 id 字段",
                    ];
                    continue;
                }

                $preview['config_menus'][] = $menuItem;

                // 检查是否存在（使用内存索引，不查询数据库）
                $exists = $this->menuExistsInMemory($menuItem, $dbMenuById, $dbMenuByUri);

                if (!$exists) {
                    // 不存在：标记为待插入
                    $preview['differences']['to_insert'][] = [
                        'module' => $moduleName,
                        'menu' => $menuItem,
                    ];
                }
                // 存在：无需更新（允许后期手动修改标题/图标/排序等）
            }
        }

        // 查找孤儿菜单
        $orphans = $this->findOrphanMenus($moduleConfigs);
        $preview['differences']['orphans'] = $orphans;

        // 检查URI冲突
        $conflicts = $this->checkUriConflicts($moduleConfigs);
        $preview['differences']['conflicts'] = $conflicts;

        return $preview;
    }

    /**
     * 同步单个菜单项
     *
     * @param array $menuItem 菜单项配置
     * @return array 同步结果
     */
    private function syncMenuItem(array $menuItem): array
    {
        // 验证菜单项必需字段
        if (!isset($menuItem['id'])) {
            return [
                'status' => 'error',
                'menu_id' => 0,
                'title' => $menuItem['title'] ?? '',
                'uri' => $menuItem['uri'] ?? '',
                'message' => '菜单配置缺少必需的 id 字段',
            ];
        }

        $result = [
            'status' => 'skipped',
            'menu_id' => $menuItem['id'],
            'title' => $menuItem['title'],
            'uri' => $menuItem['uri'] ?? '',
            'message' => '',
        ];

        // 检查菜单是否存在
        $exists = $this->menuExistsInDatabase($menuItem);

        if (!$exists) {
            // 检查父菜单是否存在（子菜单插入前需要父菜单已存在）
            if (!empty($menuItem['parent_id']) && $menuItem['parent_id'] != 0) {
                $parentExists = AdminMenu::where('id', $menuItem['parent_id'])->exists();
                if (!$parentExists) {
                    $result['status'] = 'error';
                    $result['message'] = "父菜单 {$menuItem['parent_id']} 不存在，跳过插入";
                    Log::warning("菜单 {$menuItem['id']} 的父菜单不存在", [
                        'parent_id' => $menuItem['parent_id'],
                        'title' => $menuItem['title'],
                    ]);
                    return $result;
                }
            }

            // 插入新菜单
            $this->insertMenu($menuItem);
            $result['status'] = 'inserted';
            $result['message'] = '成功插入菜单';
            return $result;
        }

        // 菜单已存在，跳过（保留手动修改）
        $result['message'] = '菜单已存在，跳过插入（保留手动修改）';
        return $result;
    }

    /**
     * 检查菜单是否存在于数据库
     *
     * @param array $menuItem 菜单项配置
     * @return bool 是否存在
     */
    private function menuExistsInDatabase(array $menuItem): bool
    {
        // 验证必需字段
        if (!isset($menuItem['id'])) {
            return false;
        }

        // 父菜单（uri为空或#占位符）使用id判断
        if (empty($menuItem['uri']) || $menuItem['uri'] === '#') {
            return AdminMenu::where('id', $menuItem['id'])->exists();
        }

        // 子菜单（有uri）使用uri判断
        return AdminMenu::where('uri', $menuItem['uri'])->exists();
    }

    /**
     * 插入菜单到数据库
     *
     * @param array $menuItem 菜单项配置
     * @return AdminMenu|null 创建的菜单模型
     */
    private function insertMenu(array $menuItem): ?AdminMenu
    {
        // 验证必需字段
        if (!isset($menuItem['id'])) {
            Log::error('插入菜单失败：缺少id字段', ['menuItem' => $menuItem]);
            return null;
        }

        return AdminMenu::create([
            'id' => $menuItem['id'],
            'parent_id' => $menuItem['parent_id'],
            'order' => $menuItem['order'] ?? $menuItem['id'],
            'title' => $menuItem['title'],
            'icon' => $menuItem['icon'],
            'uri' => $menuItem['uri'],
            'show' => 1,
            'extension' => '',  // 使用空字符串而非null
        ]);
    }

    /**
     * 检查URI冲突
     *
     * @param array $moduleConfigs 模块配置数组
     * @return array 冲突列表
     */
    private function checkUriConflicts(array $moduleConfigs): array
    {
        $conflicts = [];
        $uriMap = [];

        foreach ($moduleConfigs as $moduleName => $menuItems) {
            foreach ($menuItems as $menuItem) {
                // 检查菜单项是否有 id 字段
                if (!isset($menuItem['id'])) {
                    Log::warning("模块 {$moduleName} 的菜单项缺少 id 字段，跳过URI冲突检查", [
                        'title' => $menuItem['title'] ?? $menuItem['label'] ?? '未知',
                        'uri' => $menuItem['uri'] ?? '',
                    ]);
                    continue;
                }

                if (!empty($menuItem['uri']) && $menuItem['uri'] !== '#') {
                    $uri = $menuItem['uri'];

                    if (isset($uriMap[$uri])) {
                        $conflicts[] = [
                            'uri' => $uri,
                            'modules' => [$uriMap[$uri]['module'], $moduleName],
                            'menu_ids' => [$uriMap[$uri]['menu_id'], $menuItem['id']],
                            'message' => "URI '{$uri}' 在多个模块中重复定义",
                        ];
                    } else {
                        $uriMap[$uri] = [
                            'module' => $moduleName,
                            'menu_id' => $menuItem['id'],
                            'title' => $menuItem['title'],
                        ];
                    }
                }
            }
        }

        return $conflicts;
    }

    /**
     * 查找孤儿菜单(配置文件中不存在的菜单)
     *
     * @param array $moduleConfigs 模块配置数组
     * @return array 孤儿菜单ID列表
     */
    private function findOrphanMenus(array $moduleConfigs): array
    {
        // 收集所有配置文件中的菜单ID
        $configIds = [];
        foreach ($moduleConfigs as $moduleName => $menuItems) {
            foreach ($menuItems as $menuItem) {
                // 检查菜单项是否有 id 字段
                if (!isset($menuItem['id'])) {
                    Log::warning("模块 {$moduleName} 的菜单项缺少 id 字段", [
                        'title' => $menuItem['title'] ?? $menuItem['label'] ?? '未知',
                        'uri' => $menuItem['uri'] ?? '',
                    ]);
                    continue;
                }
                $configIds[] = $menuItem['id'];
            }
        }

        // 查找数据库中不在配置文件中的菜单
        $orphans = AdminMenu::whereNotIn('id', $configIds)
            ->pluck('id')
            ->toArray();

        return $orphans;
    }

    /**
     * 删除孤儿菜单
     *
     * @param array $orphanIds 孤儿菜单ID列表
     * @return int 删除的数量
     */
    private function deleteOrphanMenus(array $orphanIds): int
    {
        $deleted = 0;

        foreach ($orphanIds as $id) {
            // 检查是否有子菜单
            $hasChildren = AdminMenu::where('parent_id', $id)->exists();

            if ($hasChildren) {
                Log::warning("孤儿菜单 {$id} 有子菜单，跳过删除");
                continue;
            }

            // 删除菜单
            AdminMenu::where('id', $id)->delete();
            $deleted++;
        }

        return $deleted;
    }

    /**
     * 检查菜单是否存在于内存索引中
     *
     * @param array $menuItem 菜单项配置
     * @param array $dbMenuById 按ID索引的菜单数组
     * @param array $dbMenuByUri 按URI索引的菜单数组
     * @return bool 是否存在
     */
    private function menuExistsInMemory(array $menuItem, array $dbMenuById, array $dbMenuByUri): bool
    {
        // 验证必需字段
        if (!isset($menuItem['id'])) {
            return false;
        }

        // 父菜单（uri为空或#占位符）使用id判断
        if (empty($menuItem['uri']) || $menuItem['uri'] === '#') {
            return isset($dbMenuById[$menuItem['id']]);
        }

        // 子菜单（有uri）使用uri判断
        return isset($dbMenuByUri[$menuItem['uri']]);
    }

    /**
     * 获取所有已启用模块的菜单配置
     *
     * @return array 模块配置数组 [模块名 => 菜单配置]
     */
    private function getModuleMenuConfigs(): array
    {
        $configs = [];

        if (!app()->bound('modules')) {
            Log::warning('modules服务未注册，无法扫描模块');
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

            // 读取配置：优先从config获取，不可用时直接读取文件
            $configKey = 'module_' . strtolower($moduleName) . '::admin_menu';

            if (config()->has($configKey)) {
                $configs[$moduleName] = config($configKey, []);
            } else {
                $configs[$moduleName] = require $configFile;
            }
        }

        return $configs;
    }

    /**
     * 清除菜单缓存
     *
     * @return void
     */
    private function clearMenuCache(): void
    {
        // 清除Dcat Admin菜单缓存
        if (app()->bound('admin.cache')) {
            app('admin.cache')->clearAll();
        }

        // 清除Laravel配置缓存
        Artisan::call('config:clear');

        Log::info('菜单缓存已清除');
    }
}