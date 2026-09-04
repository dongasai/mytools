<?php

namespace Modules\DcatAdmin\Console;

use DLaravel\Commands\Command;
use Modules\DcatAdmin\Services\MenuSyncService;

/**
 * 同步后台菜单命令
 *
 * 将模块配置文件中的菜单同步到数据库
 */
class SyncAdminMenuCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:sync-menu
        {--module= : 指定要同步的模块名称}
        {--delete-orphan : 删除孤儿菜单（数据库中存在但配置文件中不存在的菜单）}
        {--dry-run : 预览模式，只显示差异不执行同步}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '同步模块菜单配置到数据库';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handleRun(): void
    {
        $module = $this->option('module');
        $deleteOrphan = $this->option('delete-orphan');
        $dryRun = $this->option('dry-run');

        $menuSyncService = app(MenuSyncService::class);

        $this->info('========================================');
        $this->info('后台菜单同步工具');
        $this->info('========================================');
        $this->newLine();

        // 显示参数信息
        $this->info('同步参数:');
        $this->table(
            ['参数', '值'],
            [
                ['模块', $module ?? '所有模块'],
                ['删除孤儿', $deleteOrphan ? '是' : '否'],
                ['预览模式', $dryRun ? '是' : '否'],
            ]
        );
        $this->newLine();

        if ($dryRun) {
            // 预览模式
            $this->info('执行预览模式...');
            $preview = $menuSyncService->previewSync();

            $this->displayPreview($preview);

            return;
        }

        // 执行同步
        $this->info('开始同步菜单...');

        if ($module) {
            $this->info("同步模块: {$module}");
            $result = $menuSyncService->syncModule($module);
        } else {
            $this->info('同步所有模块');
            $result = $menuSyncService->syncAll($deleteOrphan);
        }

        $this->displayResult($result, $module);
    }

    /**
     * 显示预览结果
     *
     * @param array $preview 预览数据
     * @return int 命令退出码
     */
    protected function displayPreview(array $preview): int
    {
        $this->newLine();
        $this->info('========================================');
        $this->info('预览结果');
        $this->info('========================================');
        $this->newLine();

        // 数据库菜单统计
        $this->info('1. 数据库菜单统计:');
        $this->table(
            ['项目', '数量'],
            [
                ['菜单总数', $preview['database_menus']['total']],
                ['父菜单数', $preview['database_menus']['parents']],
                ['子菜单数', $preview['database_menus']['children']],
            ]
        );
        $this->newLine();

        // 配置文件菜单统计
        $this->info('2. 配置文件菜单统计:');
        $moduleStats = [];
        foreach ($preview['modules'] as $moduleName => $info) {
            $moduleStats[] = [$moduleName, $info['menu_count']];
        }
        $moduleStats[] = ['总计', array_sum(array_column($moduleStats, 1))];
        $this->table(['模块', '菜单数'], $moduleStats);
        $this->newLine();

        // 同步差异统计
        $this->info('3. 同步差异统计:');
        $this->table(
            ['类型', '数量'],
            [
                ['待插入', count($preview['differences']['to_insert'])],
                ['待更新', count($preview['differences']['to_update'])],
                ['孤儿菜单', count($preview['differences']['orphans'])],
                ['URI冲突', count($preview['differences']['conflicts'])],
            ]
        );
        $this->newLine();

        // 详细差异
        if (!empty($preview['differences']['to_insert'])) {
            $this->info('4. 待插入菜单详情:');
            $insertItems = [];
            foreach ($preview['differences']['to_insert'] as $item) {
                $insertItems[] = [
                    $item['module'],
                    $item['menu']['id'],
                    $item['menu']['title'],
                    $item['menu']['uri'] ?? '-',
                ];
            }
            $this->table(['模块', 'ID', '标题', 'URI'], $insertItems);
            $this->newLine();
        }

        if (!empty($preview['differences']['to_update'])) {
            $this->info('5. 待更新菜单详情:');
            $updateItems = [];
            foreach ($preview['differences']['to_update'] as $item) {
                $changes = implode(', ', array_keys($item['changes']));
                $updateItems[] = [
                    $item['module'],
                    $item['menu']['id'],
                    $item['menu']['title'],
                    $changes,
                ];
            }
            $this->table(['模块', 'ID', '标题', '变更字段'], $updateItems);
            $this->newLine();
        }

        if (!empty($preview['differences']['orphans'])) {
            $this->warn('6. 孤儿菜单ID:');
            $this->line(implode(', ', $preview['differences']['orphans']));
            $this->newLine();
        }

        if (!empty($preview['differences']['conflicts'])) {
            $this->error('7. URI冲突详情:');
            $conflictItems = [];
            foreach ($preview['differences']['conflicts'] as $conflict) {
                $conflictItems[] = [
                    $conflict['uri'],
                    implode(', ', $conflict['modules']),
                    $conflict['message'],
                ];
            }
            $this->table(['URI', '冲突模块', '消息'], $conflictItems);
            $this->newLine();
        }

        $this->comment('预览模式不执行实际同步操作。');
        $this->comment('执行同步请去掉 --dry-run 参数。');

        return Command::SUCCESS;
    }

    /**
     * 显示同步结果
     *
     * @param array $result 同步结果
     * @param string|null $module 模块名
     * @return int 命令退出码
     */
    protected function displayResult(array $result, ?string $module): int
    {
        $this->newLine();
        $this->info('========================================');
        $this->info('同步结果');
        $this->info('========================================');
        $this->newLine();

        // 统计摘要
        if ($module) {
            // 单模块同步结果
            $this->info('同步统计:');
            $this->table(
                ['项目', '数量'],
                [
                    ['菜单总数', $result['total_items'] ?? 0],
                    ['插入成功', $result['inserted'] ?? 0],
                    ['更新成功', $result['updated'] ?? 0],
                    ['跳过', $result['skipped'] ?? 0],
                    ['错误', $result['errors'] ?? 0],
                ]
            );
        } else {
            // 全模块同步结果
            $this->info('同步统计:');
            $this->table(
                ['项目', '数量'],
                [
                    ['扫描模块', $result['modules_scanned'] ?? 1],
                    ['菜单总数', $result['total_menu_items'] ?? 0],
                    ['插入成功', $result['inserted'] ?? 0],
                    ['更新成功', $result['updated'] ?? 0],
                    ['跳过', $result['skipped'] ?? 0],
                    ['错误', $result['errors'] ?? 0],
                    ['孤儿菜单', $result['orphans_found'] ?? 0],
                    ['删除孤儿', $result['orphans_deleted'] ?? 0],
                ]
            );
        }

        $this->newLine();

        $this->info('执行时间: ' . ($result['duration'] ?? 'N/A') . ' 秒');
        $this->newLine();

        // 模块详情
        if (!$module && isset($result['details'])) {
            $this->info('模块同步详情:');
            foreach ($result['details'] as $moduleName => $moduleResult) {
                $this->newLine();
                $this->info("【{$moduleName} 模块】");
                $this->table(
                    ['状态', '数量'],
                    [
                        ['菜单总数', $moduleResult['total_items']],
                        ['插入成功', $moduleResult['inserted']],
                        ['更新成功', $moduleResult['updated']],
                        ['跳过', $moduleResult['skipped']],
                        ['错误', $moduleResult['errors']],
                    ]
                );
            }
        } elseif ($module) {
            $this->newLine();
            $this->info("【{$module} 模块详情】");
            $this->table(
                ['状态', '数量'],
                [
                    ['菜单总数', $result['total_items']],
                    ['插入成功', $result['inserted']],
                    ['更新成功', $result['updated']],
                    ['跳过', $result['skipped']],
                    ['错误', $result['errors']],
                ]
            );
        }

        $this->newLine();

        // 错误详情
        if (!empty($result['errors_details'])) {
            $this->error('错误详情:');
            $this->newLine();

            if (!empty($result['errors_details']['uri_conflicts'])) {
                $this->error('URI冲突:');
                $conflictItems = [];
                foreach ($result['errors_details']['uri_conflicts'] as $conflict) {
                    $conflictItems[] = [
                        $conflict['uri'],
                        implode(', ', $conflict['modules']),
                        $conflict['message'],
                    ];
                }
                $this->table(['URI', '冲突模块', '消息'], $conflictItems);
                $this->newLine();
            }

            if (!empty($result['errors_details']['exception'])) {
                $this->error('异常信息:');
                $this->error('消息: ' . $result['errors_details']['exception']['message']);
                $this->error('文件: ' . $result['errors_details']['exception']['file']);
                $this->error('行号: ' . $result['errors_details']['exception']['line']);
            }
        }

        $this->newLine();

        if (($result['errors'] ?? 0) > 0) {
            $this->error('同步完成，但有错误发生。');
            return Command::FAILURE;
        }

        $this->info('✓ 同步成功完成！');
        return Command::SUCCESS;
    }
}