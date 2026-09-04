<?php

namespace Modules\DcatAdmin\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Modules\DcatAdmin\Models\AdminMenu;

/**
 * 检查后台菜单有效性命令
 *
 * 检查后台菜单项是否有对应的控制器和路由，可选择删除无效菜单项
 */
class CheckMenuValidity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:check-menu-validity {--delete : 是否删除无效的菜单项}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '检查菜单项是否有效，包括检查是否有对应的控制器和路由';

    /**
     * 已知的有效路由前缀
     *
     * @var array
     */
    protected $validPrefixes = [
        'auth', 'dashboard', 'helpers', 'media', 'settings', 'logs', 'config',
    ];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('开始检查菜单项的有效性...');

        // 获取所有菜单项
        $menus = AdminMenu::all();

        // 获取所有路由
        $routes = Route::getRoutes();
        $routeUris = [];

        foreach ($routes as $route) {
            if (Str::startsWith($route->uri(), 'admin/')) {
                $routeUris[] = str_replace('admin/', '', $route->uri());
            }
        }

        // 获取所有模块的控制器
        $modules = [
            'Activity', 'Article', 'Farm', 'File', 'Fund', 'Game', 'GameItems',
            'OAuth', 'Pet', 'Sms', 'System', 'Task', 'Team', 'User', 'Admin',
        ];

        $controllerUris = [];

        foreach ($modules as $module) {
            $controllerDir = app_path("Module/{$module}/AdminControllers");

            if (! is_dir($controllerDir)) {
                continue;
            }

            $controllers = array_filter(scandir($controllerDir), function ($file) {
                return ! in_array($file, ['.', '..']) &&
                       pathinfo($file, PATHINFO_EXTENSION) === 'php' &&
                       ! Str::contains($file, 'Helper');
            });

            foreach ($controllers as $controller) {
                $controllerName = pathinfo($controller, PATHINFO_FILENAME);
                $uri = $this->getControllerUri($controllerName);
                $controllerUris[] = $uri;
            }
        }

        $validMenus = [];
        $invalidMenus = [];
        $parentMenus = [];

        foreach ($menus as $menu) {
            // 空URI的菜单项是父菜单（不可点击的，只用于展开子菜单）
            if (empty($menu->uri)) {
                $parentMenus[] = $menu;

                continue;
            }

            // 检查URI是否有效
            $isValid = $this->isValidUri($menu->uri, $routeUris, $controllerUris);

            if ($isValid) {
                $validMenus[] = $menu;
            } else {
                $invalidMenus[] = $menu;
            }
        }

        // 显示父菜单
        $this->info("\n父菜单（不可点击的，只用于展开子菜单）: ".count($parentMenus).' 个');

        $table = [];
        foreach ($parentMenus as $menu) {
            $table[] = [
                'id' => $menu->id,
                'title' => $menu->title,
                'parent_id' => $menu->parent_id,
                'order' => $menu->order,
                'icon' => $menu->icon,
            ];
        }

        $this->table(['ID', '标题', '父ID', '排序', '图标'], $table);

        // 显示有效的菜单项
        $this->info("\n有效的菜单项（有对应的控制器或路由）: ".count($validMenus).' 个');

        // 显示无效的菜单项
        if (count($invalidMenus) > 0) {
            $this->info("\n无效的菜单项（没有对应的控制器或路由）: ".count($invalidMenus).' 个');

            $table = [];
            foreach ($invalidMenus as $menu) {
                $table[] = [
                    'id' => $menu->id,
                    'title' => $menu->title,
                    'uri' => $menu->uri,
                    'parent_id' => $menu->parent_id,
                    'order' => $menu->order,
                ];
            }

            $this->table(['ID', '标题', 'URI', '父ID', '排序'], $table);

            // 删除无效的菜单项
            if ($this->option('delete') || $this->confirm('是否要删除这些无效的菜单项？')) {
                foreach ($invalidMenus as $menu) {
                    // 获取子菜单
                    $childMenus = AdminMenu::where('parent_id', $menu->id)->get();

                    // 更新子菜单的父ID
                    foreach ($childMenus as $childMenu) {
                        $childMenu->parent_id = $menu->parent_id;
                        $childMenu->save();
                        $this->info("  - 更新子菜单: ID = {$childMenu->id}, 标题 = {$childMenu->title}, 新父ID = {$menu->parent_id}");
                    }

                    // 删除菜单项
                    $menu->delete();
                    $this->info("  - 删除菜单项: ID = {$menu->id}, 标题 = {$menu->title}, URI = {$menu->uri}");
                }

                $this->info("\n已删除 ".count($invalidMenus).' 个无效的菜单项');
            }
        } else {
            $this->info("\n所有菜单项都是有效的！");
        }

        return 0;
    }

    /**
     * 检查URI是否有效
     */
    protected function isValidUri(string $uri, array $routeUris, array $controllerUris): bool
    {
        // 跳过已知有效的路由前缀
        foreach ($this->validPrefixes as $prefix) {
            if (Str::startsWith($uri, $prefix)) {
                return true;
            }
        }

        // 检查URI是否存在于路由中
        $uri = ltrim($uri, '/');

        // 检查路由
        foreach ($routeUris as $routeUri) {
            if (Str::startsWith($routeUri, $uri) || $routeUri === $uri) {
                return true;
            }
        }

        // 检查控制器
        foreach ($controllerUris as $controllerUri) {
            if ($controllerUri === $uri) {
                return true;
            }
        }

        return false;
    }

    /**
     * 根据控制器名称猜测URI
     */
    protected function getControllerUri(string $controllerName): string
    {
        // 移除Controller后缀
        $name = str_replace('Controller', '', $controllerName);

        // 转换为kebab-case
        $uri = Str::kebab($name);

        return $uri;
    }
}
