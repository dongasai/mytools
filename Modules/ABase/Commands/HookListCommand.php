<?php

declare(strict_types=1);

namespace Modules\ABase\Commands;

use Modules\ABase\Hooks\Management\HookManager;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\Table;

/**
 * Hook列表命令
 * php artisan hook:list
 */
class HookListCommand extends Command
{
    protected $signature = 'hook:list {--group= : 按分组显示}';

    protected $description = '列出所有可用的Hook';

    public function handle(): int
    {
        $this->info('🔗 可用Hook列表');
        $this->line('==========================================');

        $group = $this->option('group');
        $hooks = HookManager::getRegisteredHookDefinitions();

        if ($group) {
            $this->displayByGroup($hooks, $group);
        } else {
            $this->displayAll($hooks);
        }

        return self::SUCCESS;
    }

    /**
     * 显示所有Hook
     */
    private function displayAll(array $hooks): void
    {
        $table = new Table($this->output);
        $table->setHeaders(['Hook名称', '描述', '模块', '参数类', '返回类']);

        foreach ($hooks as $hookClass => $definition) {
            $hookName = class_basename($hookClass);
            $table->addRow([
                $hookName,
                wordwrap($definition->description, 50, "\n", true),
                $this->getHookGroup($hookClass),
                class_basename($definition->parameter_class),
                class_basename($definition->return_class),
            ]);
        }

        $table->render();

        $this->line('');
        $this->info('📊 统计:');
        $this->line('   总计: ' . count($hooks) . ' 个Hook');

        // 显示分组统计
        $groups = [];
        foreach ($hooks as $hookClass => $definition) {
            $group = $this->getHookGroup($hookClass);
            $groups[$group] = ($groups[$group] ?? 0) + 1;
        }

        foreach ($groups as $group => $count) {
            $this->line("   {$group}: {$count} 个");
        }
    }

    /**
     * 按分组显示Hook
     */
    private function displayByGroup(array $hooks, string $groupName): void
    {
        $filteredHooks = [];

        foreach ($hooks as $hookClass => $definition) {
            if ($this->getHookGroup($hookClass) === $groupName) {
                $filteredHooks[$hookClass] = $definition;
            }
        }

        if (empty($filteredHooks)) {
            $this->error("❌ 未找到分组 '{$groupName}' 的Hook");
            $this->line('');
            $this->info('可用分组:');
            $this->line('   Demo5 - Demo5模块');
            $this->line('   ABase - ABase模块');
            $this->line('   通用 - 通用Hook');

            return;
        }

        $this->info("📂 分组: {$groupName}");
        $this->line('');

        $table = new Table($this->output);
        $table->setHeaders(['Hook名称', '描述', '参数类', '返回类']);

        foreach ($filteredHooks as $hookClass => $definition) {
            $hookName = class_basename($hookClass);
            $table->addRow([
                $hookName,
                wordwrap($definition->description, 60, "\n", true),
                class_basename($definition->parameter_class),
                class_basename($definition->return_class),
            ]);
        }

        $table->render();

        $this->line('');
        $this->info("📊 {$groupName}分组总计: " . count($filteredHooks) . ' 个Hook');
    }

    /**
     * 获取Hook分组
     */
    private function getHookGroup(string $hookClass): string
    {
        return match (true) {
            str_contains($hookClass, 'Demo5') => 'Demo5',
            str_contains($hookClass, 'ABase') => 'ABase',
            str_contains($hookClass, 'PostContent') => 'Demo5',
            str_contains($hookClass, 'Demo') => 'ABase',
            default => '通用'
        };
    }
}
