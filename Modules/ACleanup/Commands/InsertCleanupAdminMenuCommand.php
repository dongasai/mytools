<?php

namespace Modules\AClean\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * 数据清理模块后台菜单配置命令
 *
 * 用于配置 AClean 模块的后台管理菜单
 */
class InsertCleanupAdminMenuCommand extends Command
{
    /**
     * 命令签名
     */
    protected $signature = 'aclean:insert-admin-menu {--force : 强制重新创建菜单}';

    /**
     * 命令描述
     */
    protected $description = '配置 数据清理 模块后台管理菜单';

    /**
     * 执行命令
     */
    public function handle()
    {
        $this->info('开始配置 数据清理 模块后台管理菜单...');

        // 检查是否强制重新创建
        $force = $this->option('force');

        // 检查菜单是否已存在
        $existingMenu = DB::table('admin_menu')->where('title', '数据备份与清理管理')->first();
        if ($existingMenu && ! $force) {
            $this->warn('数据备份与清理管理菜单已存在，使用 --force 参数强制重新创建');

            return;
        }

        if ($existingMenu && $force) {
            $this->info('删除现有菜单...');
            $this->deleteExistingMenus();
        }

        // 创建菜单
        $this->createMenus();

        $this->info('备份与清理 模块后台管理菜单配置完成！');
    }

    /**
     * 删除现有菜单
     */
    protected function deleteExistingMenus()
    {
        // 查找主菜单
        $mainMenu = DB::table('admin_menu')->where('title', '数据备份与清理管理')->first();

        if ($mainMenu) {
            // 删除所有子菜单
            DB::table('admin_menu')->where('parent_id', $mainMenu->id)->delete();

            // 删除主菜单
            DB::table('admin_menu')->where('id', $mainMenu->id)->delete();

            $this->info('已删除现有菜单');
        }
    }

    /**
     * 创建菜单
     */
    protected function createMenus()
    {
        // 获取超管工具菜单ID (parent_id = 107)
        $superAdminToolsId = 107;

        // 获取当前最大order值
        $maxOrder = DB::table('admin_menu')->max('order');
        $currentOrder = $maxOrder + 1;

        // 1. 创建数据备份与清理管理主菜单
        $mainMenuId = DB::table('admin_menu')->insertGetId([
            'parent_id' => $superAdminToolsId,
            'order' => $currentOrder++,
            'title' => '数据备份与清理管理',
            'icon' => 'fa-trash-o',
            'uri' => '',
            'show' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->info("创建主菜单: 数据备份与清理管理 (ID: {$mainMenuId})");

        // 2. 创建子菜单
        $subMenus = [
            [
                'title' => '清理配置',
                'icon' => 'fa-cog',
                'uri' => 'module_aclean/configs',
                'description' => '配置各表的清理规则',
            ],
            [
                'title' => '清理计划',
                'icon' => 'fa-calendar',
                'uri' => 'module_aclean/plans',
                'description' => '管理清理计划',
            ],
            [
                'title' => '清理任务',
                'icon' => 'fa-tasks',
                'uri' => 'module_aclean/tasks',
                'description' => '查看和管理清理任务',
            ],
            [
                'title' => '数据备份',
                'icon' => 'fa-database',
                'uri' => 'module_aclean/backups',
                'description' => '管理数据备份',
            ],
            [
                'title' => '清理日志',
                'icon' => 'fa-file-text-o',
                'uri' => 'module_aclean/logs',
                'description' => '查看清理操作日志',
            ],
            [
                'title' => '统计信息',
                'icon' => 'fa-bar-chart',
                'uri' => 'module_aclean/stats',
                'description' => '查看统计数据',
            ],
            [
                'title' => '独立备份计划',
                'icon' => 'fa-archive',
                'uri' => 'module_aclean/backup-plans',
                'description' => '管理独立备份计划',
            ],
        ];

        foreach ($subMenus as $menu) {
            $subMenuId = DB::table('admin_menu')->insertGetId([
                'parent_id' => $mainMenuId,
                'order' => $currentOrder++,
                'title' => $menu['title'],
                'icon' => $menu['icon'],
                'uri' => $menu['uri'],
                'show' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->info("创建子菜单: {$menu['title']} (ID: {$subMenuId}) - {$menu['description']}");
        }

        $this->info('所有菜单创建完成！');
    }
}
