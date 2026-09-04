<?php

declare(strict_types=1);

namespace Modules\Application\Console;

use DLaravel\Commands\Command;
use Modules\ABase\Hooks\Management\Hooks;
use Modules\Application\Hooks\Definitions\OrganizationTypeHookDefinition;
use Modules\Application\Hooks\Handlers\OrganizationTypeHookHandler;
use Modules\Application\Hooks\Parameters\OrganizationTypeHookParameter;

/**
 * 组织类型Hook控制台命令
 *
 * 用于测试和调试组织类型Hook功能
 */
class OrganizationTypeHookCommand extends Command
{
    /**
     * 命令名称
     */
    protected $signature = 'application:hook:organization-type
                            {--source=application : 源模块 (admin, shop, workflow)}
                            {--user-id= : 用户ID}
                            {--org-type= : 组织类型过滤}
                            {--include-system= : 是否包含系统类型 (true, false)}
                            {--debug : 显示调试信息}';

    /**
     * 命令描述
     */
    protected $description = '应用组织类型Hook并返回结果';

    /**
     * 执行命令
     */
    public function handleRun(): void
    {
        $this->info('🏢 执行组织类型Hook...');

        // 获取命令选项
        $source = $this->option('source') ?? 'admin';
        $userId = $this->option('user-id') ? (int) $this->option('user-id') : null;
        $orgType = $this->option('org-type');
        $includeSystem = $this->option('include-system') === 'true';
        $debug = $this->option('debug');

        if ($debug) {
            Hooks::enableDebug();
        }

        // 注意：Hook处理器应在ServiceProvider中统一注册，这里仅用于测试
        // 检查处理器是否已注册
        $handlers = Hooks::getHandlers(OrganizationTypeHookDefinition::class);
        if (empty($handlers)) {
            $this->warn('⚠️ 组织类型Hook处理器未注册，请检查ServiceProvider配置');
            $this->warn('预期处理器: OrganizationTypeHookHandler');
        }

        // 创建Hook参数
        $parameter = OrganizationTypeHookParameter::create(
            source: $source,
            user_id: $userId,
            org_type: $orgType,
            include_system: $includeSystem
        );

        $this->line('📋 Hook参数:');
        $this->table(['参数', '值'], [
            ['源模块', $parameter->source],
            ['用户ID', $parameter->user_id ?? 'null'],
            ['组织类型', $parameter->org_type ?? 'null'],
            ['包含系统类型', $parameter->include_system ? 'true' : 'false'],
        ]);

        // 应用Hook
        $this->line('⚡ 正在应用Hook...');
        $result = Hooks::apply(OrganizationTypeHookDefinition::class, $parameter);

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
            ['组织类型数量', $result->getCount()],
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

        // 组织类型列表
        if ($result->isNotEmpty()) {
            $this->newLine();
            $this->info('🏢 组织类型列表:');

            $types = $result->getOrganizationTypes();
            $tableData = [];

            foreach ($types as $type) {
                $tableData[] = [
                    'ID' => $type->type_id,
                    '名称' => $type->type_name,
                    '描述' => $type->description ?? '-',
                    '系统类型' => $type->isSystemType() ? '是' : '否',
                ];
            }

            $this->table(['类型ID', '名称', '描述', '系统类型'], $tableData);

            // 选项格式
            $this->newLine();
            $this->info('📝 表单选项格式:');
            $options = $result->getTypeOptions();
            foreach ($options as $key => $value) {
                $this->line("  [$key] => $value");
            }
        }

        // 元数据
        if (! empty($result->metadata)) {
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
            if (! empty($debugInfo['execution_log'])) {
                $this->line('📝 执行日志:');
                foreach ($debugInfo['execution_log'] as $log) {
                    $this->line("  • $log");
                }
                $this->newLine();
            }

            // 处理器信息
            if (! empty($debugInfo['handlers'])) {
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
            if (! empty($debugInfo['subscribers'])) {
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
