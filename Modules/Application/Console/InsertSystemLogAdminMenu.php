<?php

namespace Modules\Application\Console;

use Illuminate\Console\Command;
use Modules\DcatAdmin\Models\AdminMenu;

/**
 * 添加系统日志管理菜单命令
 */
class InsertSystemLogAdminMenu extends Command
{
    /**
     * 命令签名
     *
     * @var string
     */
    protected $signature = 'application:insert-log-menu {--force : 强制重新创建菜单}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '添加系统日志管理菜单到后台管理系统';

    /**
     * 调试工具父菜单ID
     *
     * @var int
     */
    protected $debugToolsParentId = 205;

    /**
     * 执行命令
     */
    public function handle(): int
    {
        try {
            // 检查父菜单是否存在
            if (! $this->checkParentMenu()) {
                $this->error("调试工具父菜单 (ID: {$this->debugToolsParentId}) 不存在");

                return 1;
            }

            // 检查是否强制重新创建
            if ($this->option('force')) {
                $this->deleteExistingMenu();
            }

            // 创建系统日志管理菜单
            $this->createSystemLogMenu();

            $this->info('系统日志管理菜单添加成功！');

            return 0;

        } catch (\Exception $e) {
            $this->error('添加系统日志管理菜单失败: '.$e->getMessage());

            return 1;
        }
    }

    /**
     * 检查父菜单是否存在
     */
    protected function checkParentMenu(): bool
    {
        return AdminMenu::where('id', $this->debugToolsParentId)->exists();
    }

    /**
     * 删除现有菜单
     */
    protected function deleteExistingMenu(): void
    {
        $existingMenu = AdminMenu::where('title', '系统日志')->first();
        if ($existingMenu) {
            $existingMenu->delete();
            $this->info("删除现有系统日志菜单 (ID: {$existingMenu->id})");
        }
    }

    /**
     * 创建系统日志管理菜单
     */
    protected function createSystemLogMenu(): void
    {
        // 获取当前最大order值
        $maxOrder = AdminMenu::where('parent_id', $this->debugToolsParentId)->max('order');
        $nextOrder = $maxOrder ? $maxOrder + 1 : 163;

        // 创建系统日志菜单
        $systemLogMenu = AdminMenu::firstOrCreate(
            [
                'title' => '系统日志',
                'parent_id' => $this->debugToolsParentId,
            ],
            [
                'uri' => 'system-logs',
                'icon' => 'fa-file-text',
                'order' => $nextOrder,
                'show' => 1,
                'extension' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->info("✓ 添加系统日志菜单: {$systemLogMenu->title}, ID: {$systemLogMenu->id}, Order: {$systemLogMenu->order}");
    }
}
