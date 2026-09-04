<?php

declare(strict_types=1);

namespace Modules\ABase\Commands;

use Modules\ABase\Hooks\Definitions\DemoHook;
use Modules\ABase\Hooks\Management\Hooks;
use Modules\ABase\Hooks\Parameters\DemoHookParameter;
use Modules\ABase\Hooks\Results\DemoHookResult;
use Illuminate\Console\Command;

/**
 * Hook测试命令
 * php artisan hook:test
 */
class HookTestCommand extends Command
{
    protected $signature = 'hook:test';

    protected $description = '测试指定的Hook功能';

    public function handle(): int
    {
        $this->info('开始测试 Hook 系统...');

        try {
            // 启用调试模式
            Hooks::enableDebug();
            $this->info('✓ 已启用 Hook 调试模式');

            // 创建测试参数
            $parameter = DemoHookParameter::create(
                name: 'test_hook',
                value: 42,
                options: ['source' => 'HookTestCommand'],
                enabled: true,
                description: '测试DemoHook功能'
            );

            $this->info('✓ 已创建 DemoHook 参数');
            $this->line('  参数详情: ' . json_encode($parameter->toArray(), JSON_UNESCAPED_UNICODE));

            // Hook处理器和Subscriber已经在服务提供者中注册，无需手动注册

            // 检查Hook是否已注册
            if (! Hooks::hasHandlers(DemoHook::class)) {
                $this->error('✗ DemoHook 未注册任何处理器');

                return 1;
            }

            $this->info('✓ DemoHook 已注册处理器');
            $this->line('  处理器数量: ' . Hooks::countHandlers(DemoHook::class));

            // 执行Hook - 使用完整的Hook系统，包括Handler和Subscriber
            $this->line('正在执行 DemoHook...');
            $result = Hooks::apply(DemoHook::class, $parameter);

            // 检查执行结果
            if ($result->isSuccess()) {
                $this->info('✓ DemoHook 执行成功');

                if ($result instanceof DemoHookResult) {
                    $this->line('  处理后名称: ' . $result->getProcessedName());
                    $this->line('  处理后值: ' . $result->getProcessedValue());
                    $this->line('  处理时间: ' . $result->getProcessingTime());
                    $this->line('  应用选项: ' . json_encode($result->getAppliedOptions(), JSON_UNESCAPED_UNICODE));
                }
            } else {
                $this->error('✗ DemoHook 执行失败');
                $this->line('  错误信息: ' . implode(', ', $result->getErrors()));

                return 1;
            }

            // 显示调试信息
            $debugInfo = Hooks::debug();
            $this->line('');
            $this->info('=== Hook 系统调试信息 ===');
            $this->line('已注册Hook数量: ' . $debugInfo['hook_count']);
            $this->line('调试模式: ' . ($debugInfo['debug_mode'] ? '启用' : '禁用'));
            $this->line('当前执行深度: ' . $debugInfo['current_execution_depth']);

            if (! empty($debugInfo['execution_log'])) {
                $this->line('');
                $this->info('执行日志:');
                foreach ($debugInfo['execution_log'] as $log) {
                    $this->line('  - ' . $log);
                }
            }

            $this->info('✓ Hook 系统测试完成');

            return 0;
        } catch (\Exception $e) {
            $this->error('✗ Hook 测试过程中发生异常');
            $this->line('错误信息: ' . $e->getMessage());
            $this->line('文件位置: ' . $e->getFile() . ':' . $e->getLine());

            if (config('app.debug')) {
                $this->line('堆栈跟踪: ' . $e->getTraceAsString());
            }

            return 1;
        }
    }
}
