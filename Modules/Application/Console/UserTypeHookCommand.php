<?php

declare(strict_types=1);

namespace Modules\Application\Console;

use DLaravel\Commands\Command;
use Modules\ABase\Hooks\Management\Hooks;
use Modules\Application\Hooks\Definitions\UserTypeHookDefinition;
use Modules\Application\Hooks\Parameters\UserTypeHookParameter;
use Modules\Application\Hooks\Handlers\UserTypeHookHandler;

/**
 * 用户类型Hook控制台命令
 *
 * 用于测试和调试用户类型Hook功能
 */
class UserTypeHookCommand extends Command
{
    /**
     * 命令名称
     */
    protected $signature = 'application:hook:user-type
                            {--source=admin : 源模块 (admin, shop, workflow)}
                            {--include-system= : 是否包含系统类型 (true, false)}
                            {--active-only= : 是否只获取活跃类型 (true, false)}
                            {--debug : 显示调试信息}';

    /**
     * 命令描述
     */
    protected $description = '应用用户类型Hook并返回结果';

    /**
     * 执行命令
     */
    public function handleRun(): void
    {
        $this->info('🏷️ 执行用户类型Hook...');

        // 获取命令选项
        $source = $this->option('source') ?? 'admin';
        $includeSystem = $this->option('include-system') === 'true';
        $activeOnly = $this->option('active-only') === 'true';
        $debug = $this->option('debug');

        if ($debug) {
            Hooks::enableDebug();
        }

        // 创建Hook参数
        $parameter = UserTypeHookParameter::create(
            source: $source,
            includeSystem: $includeSystem,
            activeOnly: $activeOnly
        );

        $this->line('📋 Hook参数:');
        $this->table(['参数', '值'], [
            ['源模块', $parameter->source],
            ['包含系统类型', $parameter->include_system ? 'true' : 'false'],
            ['只获取活跃类型', $parameter->active_only ? 'true' : 'false'],
        ]);

        // 注意：Hook处理器应在ServiceProvider中统一注册，这里仅用于测试
        // 检查处理器是否已注册
        $handlers = Hooks::getHandlers(UserTypeHookDefinition::class);
        if (empty($handlers)) {
            $this->warn('⚠️ 用户类型Hook处理器未注册，请检查ServiceProvider配置');
            $this->warn('预期处理器: UserTypeHookHandler');
        }

        // 应用Hook
        $this->line('⚡ 正在应用Hook...');
        $result = Hooks::apply(UserTypeHookDefinition::class, $parameter);

        // 输出结果
        $this->outputResult($result, $debug);
    }

    /**
     * 输出Hook结果
     */
    private function outputResult($result, bool $debug): void
    {
        $this->newLine();
        $this->info('📊 Hook执行结果:');

        // 基本信息
        $this->table(['属性', '值'], [
            ['执行状态', $result->isSuccess() ? '✅ 成功' : '❌ 失败'],
            ['处理消息', $result->getMessage()],
            ['用户类型数量', $result->getCount()],
            ['是否为空', $result->isEmpty() ? '是' : '否'],
        ]);

        // 错误信息
        if ($result->hasErrors()) {
            $this->newLine();
            $this->error('⚠️ 错误信息:');
            foreach ($result->getErrors() as $error) {
                $this->line("  • $error");
            }
        }

        // 用户类型列表
        if ($result->isNotEmpty()) {
            $this->newLine();
            $this->info('🏷️ 用户类型列表:');

            $userTypes = $result->getUserTypes();
            $tableData = [];

            foreach ($userTypes as $userType) {
                $tableData[] = [
                    'ID' => $userType->id,
                    '名称' => $userType->name,
                    '代码' => $userType->code,
                    '描述' => $userType->description ?? '-',
                    '系统类型' => $userType->is_system ? '是' : '否',
                    '状态' => $userType->is_active ? '活跃' : '非活跃',
                ];
            }

            $this->table(['ID', '名称', '代码', '描述', '系统类型', '状态'], $tableData);

            // 选项格式
            $this->newLine();
            $this->info('📝 表单选项格式:');
            $options = $result->getTypeOptions();
            foreach ($options as $key => $value) {
                $this->line("  [$key] => $value");
            }

            // 按状态分组
            $this->newLine();
            $this->info('📋 按状态分组:');

            $activeTypes = $result->getActiveTypes();
            $systemTypes = $result->getSystemTypes();
            $customTypes = $result->getCustomTypes();

            $this->line("  ✅ 活跃类型: " . count($activeTypes) . "个");
            if (!empty($activeTypes)) {
                foreach ($activeTypes as $type) {
                    $this->line("    • {$type->name} ({$type->code})");
                }
            }

            $this->line("  🔧 系统类型: " . count($systemTypes) . "个");
            if (!empty($systemTypes)) {
                foreach ($systemTypes as $type) {
                    $this->line("    • {$type->name} ({$type->code})");
                }
            }

            $this->line("  🎨 自定义类型: " . count($customTypes) . "个");
            if (!empty($customTypes)) {
                foreach ($customTypes as $type) {
                    $this->line("    • {$type->name} ({$type->code})");
                }
            }
        }

        // 元数据
        if (!empty($result->metadata)) {
            $this->newLine();
            $this->info('📋 元数据:');
            $this->line(json_encode($result->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        // 调试信息
        if ($debug) {
            $this->newLine();
            $this->info('🐛 调试信息:');
            $debugInfo = Hooks::debug();

            // 执行日志
            if (!empty($debugInfo['execution_log'])) {
                $this->line('📝 执行日志:');
                foreach ($debugInfo['execution_log'] as $log) {
                    $this->line("  • $log");
                }
                $this->newLine();
            }

            // 处理器信息
            if (!empty($debugInfo['handlers'])) {
                $this->line('⚙️ 处理器信息:');
                foreach ($debugInfo['handlers'] as $hookClass => $handlers) {
                    $hookName = class_basename($hookClass);
                    $this->line("  🔗 $hookName:");
                    foreach ($handlers as $handler) {
                        $priority = $handler['priority'];
                        $type = $handler['type'];
                        $name = $handler['handler'];
                        $this->line("    • [$priority] $name ($type)");
                    }
                }
                $this->newLine();
            }

            // 订阅者信息
            if (!empty($debugInfo['subscribers'])) {
                $this->line('📋 订阅者信息:');
                foreach ($debugInfo['subscribers'] as $hookClass => $subscribers) {
                    $hookName = class_basename($hookClass);
                    if (!empty($subscribers)) {
                        $this->line("  📝 $hookName:");
                        foreach ($subscribers as $subscriber) {
                            $subscriberName = $subscriber['subscriber'];
                            $method = $subscriber['method'];
                            $instance = $subscriber['instance'] ? class_basename($subscriber['instance']) : 'N/A';
                            $this->line("    • $subscriberName::$method ($instance)");
                        }
                    }
                }
                $this->newLine();
            }

            // 统计信息
            $this->line('📊 统计信息:');
            $this->table(['项目', '数量'], [
                ['已注册Hook', count($debugInfo['registered_hooks'])],
                ['处理器总数', $debugInfo['total_handlers'] ?? 0],
                ['订阅者总数', $debugInfo['total_subscribers'] ?? 0],
                ['调试模式', $debugInfo['debug_mode'] ? '开启' : '关闭'],
                ['当前执行深度', $debugInfo['current_execution_depth'] ?? 0],
            ]);
        }
    }
}
