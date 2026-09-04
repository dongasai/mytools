<?php

declare(strict_types=1);

namespace Modules\ABase\Commands;

use Modules\ABase\Hooks\Management\Hooks;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\Table;

/**
 * Hook状态查看命令
 */
class HookStatusCommand extends Command
{
    protected $signature = 'hook:status {--details : 显示详细信息}';

    protected $description = '显示Hook系统状态和统计信息';

    public function handle(): int
    {
        $this->info('🔧 Hook系统状态报告');
        $this->line('==========================================');

        try {
            $hooks = Hooks::all();
            $details = $this->option('details');

            // 基本统计
            $this->displayStatistics($hooks);

            if ($details) {
                $this->line('');
                $this->displayDetailedInformation($hooks);
            }

            // 系统健康检查
            $this->line('');
            $this->displayHealthCheck();

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ 获取Hook状态失败: {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    /**
     * 显示统计信息
     */
    private function displayStatistics(array $hooks): void
    {
        $totalHooks = count($hooks);
        $totalHandlers = 0;
        $hookCategories = [];

        foreach ($hooks as $name => $handlers) {
            $handlerCount = count($handlers);
            $totalHandlers += $handlerCount;

            // 分类统计
            $category = $this->getHookCategory($name);
            if (! isset($hookCategories[$category])) {
                $hookCategories[$category] = ['hooks' => 0, 'handlers' => 0];
            }
            $hookCategories[$category]['hooks']++;
            $hookCategories[$category]['handlers'] += $handlerCount;
        }

        $this->info('📊 统计信息:');
        $this->line("   总Hook数量: {$totalHooks}");
        $this->line("   总处理器数量: {$totalHandlers}");
        $this->line('   平均每个Hook的处理器: ' . ($totalHooks > 0 ? round($totalHandlers / $totalHooks, 2) : 0));

        // 分类统计表格
        if (! empty($hookCategories)) {
            $this->line('');
            $this->info('📂 分类统计:');
            $table = new Table($this->output);
            $table->setHeaders(['分类', 'Hook数量', '处理器数量']);

            foreach ($hookCategories as $category => $stats) {
                $table->addRow([
                    $category,
                    $stats['hooks'],
                    $stats['handlers'],
                ]);
            }
            $table->render();
        }
    }

    /**
     * 显示详细信息
     */
    private function displayDetailedInformation(array $hooks): void
    {
        $this->info('📋 详细信息:');

        foreach ($hooks as $hookName => $handlers) {
            $this->line("   🎯 {$hookName}");

            if (empty($handlers)) {
                $this->line('      ℹ️  无处理器');

                continue;
            }

            // 按优先级排序处理器
            $sortedHandlers = $handlers;
            usort($sortedHandlers, fn($a, $b) => $b->getPriority() - $a->getPriority());

            foreach ($sortedHandlers as $handler) {
                $priority = $handler->getPriority();
                $description = $handler->getDescription();
                $className = class_basename($handler);

                $this->line("      📌 [{$priority}] {$className}");
                $this->line("         {$description}");
            }
            $this->line('');
        }
    }

    /**
     * 显示健康检查
     */
    private function displayHealthCheck(): void
    {
        $this->info('🔍 系统健康检查:');

        $checks = $this->performHealthChecks();

        foreach ($checks as $check => $result) {
            $status = $result['status'] ? '✅' : '❌';
            $message = $result['message'];

            $this->line("   {$status} {$check}: {$message}");
        }
    }

    /**
     * 执行健康检查
     */
    private function performHealthChecks(): array
    {
        return [
            'Hook管理器' => $this->checkHookManager(),
            '核心Hook定义' => $this->checkCoreHookDefinitions(),
            '处理器注册' => $this->checkHandlerRegistration(),
            '内存使用' => $this->checkMemoryUsage(),
            '文件权限' => $this->checkFilePermissions(),
        ];
    }

    /**
     * 检查Hook管理器
     */
    private function checkHookManager(): array
    {
        try {
            $hooks = Hooks::all();

            return [
                'status' => true,
                'message' => 'Hook管理器运行正常，已注册 ' . count($hooks) . ' 个Hook',
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Hook管理器异常: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * 检查核心Hook定义
     */
    private function checkCoreHookDefinitions(): array
    {
        try {
            $coreHookClass = 'Modules\ABase\Hooks\Definitions\CoreHooks';
            if (! class_exists($coreHookClass)) {
                return ['status' => false, 'message' => 'CoreHooks类不存在'];
            }

            $coreHooks = $coreHookClass::all();
            $expectedCount = 18; // 预期的核心Hook数量

            if (count($coreHooks) < $expectedCount) {
                return [
                    'status' => false,
                    'message' => "核心Hook定义不完整 (期望: {$expectedCount}, 实际: " . count($coreHooks) . ')',
                ];
            }

            return [
                'status' => true,
                'message' => '所有核心Hook定义完整 (' . count($coreHooks) . '/18)',
            ];
        } catch (\Exception $e) {
            return ['status' => false, 'message' => '检查核心Hook定义失败: ' . $e->getMessage()];
        }
    }

    /**
     * 检查处理器注册
     */
    private function checkHandlerRegistration(): array
    {
        try {
            $hooks = Hooks::all();
            $hooksWithoutHandlers = [];

            foreach ($hooks as $name => $handlers) {
                if (empty($handlers)) {
                    $hooksWithoutHandlers[] = $name;
                }
            }

            if (! empty($hooksWithoutHandlers)) {
                return [
                    'status' => false,
                    'message' => count($hooksWithoutHandlers) . ' 个Hook没有处理器: ' . implode(', ', array_slice($hooksWithoutHandlers, 0, 3)),
                ];
            }

            return ['status' => true, 'message' => '所有Hook都有处理器'];
        } catch (\Exception $e) {
            return ['status' => false, 'message' => '检查处理器注册失败: ' . $e->getMessage()];
        }
    }

    /**
     * 检查内存使用
     */
    private function checkMemoryUsage(): array
    {
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = ini_get('memory_limit');

        $usagePercent = $this->calculateMemoryUsagePercent($memoryUsage, $memoryLimit);

        if ($usagePercent > 80) {
            return [
                'status' => false,
                'message' => "内存使用过高: {$usagePercent}% ({$this->formatBytes($memoryUsage)})",
            ];
        }

        return [
            'status' => true,
            'message' => "内存使用正常: {$usagePercent}% ({$this->formatBytes($memoryUsage)})",
        ];
    }

    /**
     * 检查文件权限
     */
    private function checkFilePermissions(): array
    {
        $hookPath = module_path('ABase', 'Hooks');

        if (! is_dir($hookPath)) {
            return ['status' => false, 'message' => 'Hooks目录不存在'];
        }

        if (! is_readable($hookPath)) {
            return ['status' => false, 'message' => 'Hooks目录不可读'];
        }

        if (! is_writable($hookPath)) {
            return ['status' => false, 'message' => 'Hooks目录不可写'];
        }

        return ['status' => true, 'message' => 'Hooks目录权限正常'];
    }

    /**
     * 获取Hook分类
     */
    private function getHookCategory(string $hookName): string
    {
        if (str_starts_with($hookName, 'module_')) {
            return '模块相关';
        }
        if (str_starts_with($hookName, 'controller_')) {
            return '控制器相关';
        }
        if (str_starts_with($hookName, 'validation_')) {
            return '验证相关';
        }
        if (str_starts_with($hookName, 'cache_')) {
            return '缓存相关';
        }
        if (str_starts_with($hookName, 'statistics_')) {
            return '统计相关';
        }
        if (str_starts_with($hookName, 'data_')) {
            return '数据相关';
        }
        if (str_starts_with($hookName, 'system_')) {
            return '系统相关';
        }

        return '其他';
    }

    /**
     * 计算内存使用百分比
     */
    private function calculateMemoryUsagePercent(int $usage, string $limit): float
    {
        if ($limit === '-1') {
            return 0; // 无限制
        }

        $limitBytes = $this->parseMemoryLimit($limit);

        return ($usage / $limitBytes) * 100;
    }

    /**
     * 解析内存限制
     */
    private function parseMemoryLimit(string $limit): int
    {
        $unit = strtoupper(substr($limit, -1));
        $value = (int) substr($limit, 0, -1);

        return match ($unit) {
            'G' => $value * 1024 * 1024 * 1024,
            'M' => $value * 1024 * 1024,
            'K' => $value * 1024,
            default => (int) $limit
        };
    }

    /**
     * 格式化字节数
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;

        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }

        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }
}
