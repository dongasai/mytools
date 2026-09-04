<?php

namespace Modules\DcatAdmin\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Modules\DcatAdmin\Models\AdminMenu;

/**
 * 检查特定后台菜单命令
 *
 * 检查特定ID的后台菜单项是否指向有效的控制器，支持批量检查和删除无效菜单
 */
class CheckSpecificMenus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:check-specific-menus {id?} {--all : 检查所有菜单项} {--delete : 是否删除无效的菜单项}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '检查特定ID的菜单项是否指向有效的控制器，并可选择删除无效的菜单项';

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
        $id = $this->argument('id');
        $checkAll = $this->option('all');

        if (! $id && ! $checkAll) {
            $this->error('请提供菜单ID或使用--all选项检查所有菜单项');

            return 1;
        }

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

        if ($id) {
            // 检查特定ID的菜单项
            $menu = AdminMenu::find($id);

            if (! $menu) {
                $this->error("未找到ID为{$id}的菜单项");

                return 1;
            }

            $this->info("检查ID为{$id}的菜单项: {$menu->title}, URI: {$menu->uri}");

            $isValid = $this->isValidMenu($menu, $routeUris, $controllerUris);

            if ($isValid) {
                $this->info('菜单项指向有效的控制器');
            } else {
                $this->error('菜单项不指向有效的控制器');

                if ($this->option('delete') || $this->confirm('是否要删除此菜单项？')) {
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
                    $this->info("  - 已删除菜单项: ID = {$menu->id}, 标题 = {$menu->title}, URI = {$menu->uri}");
                }
            }
        } else {
            // 检查所有菜单项
            $menus = AdminMenu::all();
            $invalidMenus = [];

            foreach ($menus as $menu) {
                // 跳过空URI的菜单项（父菜单）
                if (empty($menu->uri)) {
                    continue;
                }

                $isValid = $this->isValidMenu($menu, $routeUris, $controllerUris);

                if (! $isValid) {
                    $invalidMenus[] = $menu;
                }
            }

            // 显示无效的菜单项
            if (count($invalidMenus) > 0) {
                $this->info("\n发现 ".count($invalidMenus).' 个无效的菜单项:');

                $table = [];
                foreach ($invalidMenus as $menu) {
                    $table[] = [
                        'id' => $menu->id,
                        'title' => $menu->title,
                        'uri' => $menu->uri,
                        'parent_id' => $menu->parent_id,
                    ];
                }

                $this->table(['ID', '标题', 'URI', '父ID'], $table);

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
                $this->info("\n所有菜单项都指向有效的控制器！");
            }
        }

        return 0;
    }

    /**
     * 检查菜单项是否有效
     */
    protected function isValidMenu(AdminMenu $menu, array $routeUris, array $controllerUris): bool
    {
        // 跳过空URI的菜单项（父菜单）
        if (empty($menu->uri)) {
            return true;
        }

        // 跳过已知有效的路由前缀
        foreach ($this->validPrefixes as $prefix) {
            if (Str::startsWith($menu->uri, $prefix)) {
                return true;
            }
        }

        // 检查URI是否存在于路由中
        $uri = ltrim($menu->uri, '/');

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
