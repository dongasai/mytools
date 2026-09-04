<?php

declare(strict_types=1);

namespace Modules\Application\Console;

use DLaravel\Commands\Command;
use Modules\ABase\Hooks\Management\Hooks;
use Modules\Application\Hooks\Definitions\SuperiorHookDefinition;
use Modules\Application\Hooks\Parameters\SuperiorHookParameter;

/**
 * 上级关系Hook控制台命令
 *
 * 用于测试和调试上级关系Hook功能
 */
class SuperiorHookCommand extends Command
{
    /**
     * 命令名称
     */
    protected $signature = 'application:hook:superior
                            {--source=application : 源模块 (admin, shop, workflow)}
                            {--user-id= : 用户ID (必需)}
                            {--org-type= : 组织类型}
                            {--relationship= : 关系类型 (direct, indirect, all)}
                            {--max-level= : 最大层级}
                            {--include-inactive= : 是否包含非活跃上级 (true, false)}
                            {--debug : 显示调试信息}';

    /**
     * 命令描述
     */
    protected $description = '应用上级关系Hook并返回结果';

    /**
     * 执行命令
     */
    public function handleRun(): void
    {
        $this->info('👆 执行上级关系Hook...');

        // 获取命令选项
        $source = $this->option('source') ?? 'admin';
        $userId = $this->option('user-id');
        $orgType = $this->option('org-type');
        $relationship = $this->option('relationship') ?? 'all';
        $maxLevel = $this->option('max-level') ? (int) $this->option('max-level') : null;
        $includeInactive = $this->option('include-inactive') === 'true';
        $debug = $this->option('debug');

        if (! $userId) {
            $this->error('❌ 用户ID是必需的参数，请使用 --user-id= 选项指定');

            return;
        }

        if ($debug) {
            Hooks::enableDebug();
        }

        // 创建Hook参数
        $parameter = SuperiorHookParameter::create(
            source: $source,
            userId: (int) $userId,
            orgType: $orgType,
            relationship: $relationship,
            maxLevel: $maxLevel,
            includeInactive: $includeInactive
        );

        $this->line('📋 Hook参数:');
        $this->table(['参数', '值'], [
            ['源模块', $parameter->source],
            ['用户ID', $parameter->user_id],
            ['组织类型', $parameter->org_type ?? 'null'],
            ['关系类型', $parameter->relationship],
            ['最大层级', $parameter->max_level ?? 'null'],
            ['包含非活跃上级', $parameter->include_inactive ? 'true' : 'false'],
        ]);

        // 应用Hook
        $this->line('⚡ 正在应用Hook...');
        $result = Hooks::apply(SuperiorHookDefinition::class, $parameter);

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
            ['上级数量', $result->getCount()],
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

        // 上级列表
        if ($result->isNotEmpty()) {
            $this->newLine();
            $this->info('👆 上级关系列表:');

            $superiors = $result->getSuperiors();
            $tableData = [];

            foreach ($superiors as $superior) {
                $tableData[] = [
                    'ID' => $superior['id'],
                    '姓名' => $superior['name'],
                    '关系类型' => $superior['relationship'],
                    '层级' => $superior['level'],
                    '用户类型' => $superior['user_type'],
                    '部门' => $superior['department_id'] ?? '-',
                    '状态' => $superior['is_active'] ? '活跃' : '非活跃',
                ];
            }

            $this->table([
                'ID',
                '姓名',
                '关系类型',
                '层级',
                '用户类型',
                '部门ID',
                '状态',
            ], $tableData);

            // 按关系类型分组
            $this->newLine();
            $this->info('📋 按关系类型分组:');

            $directSuperiors = $result->getDirectSuperiors();
            $indirectSuperiors = $result->getIndirectSuperiors();

            $this->line('  🎯 直接上级: ' . count($directSuperiors) . '个');
            if (! empty($directSuperiors)) {
                foreach ($directSuperiors as $superior) {
                    $this->line("    • {$superior['name']} (层级: {$superior['level']})");
                }
            }

            $this->line('  🔄 间接上级: ' . count($indirectSuperiors) . '个');
            if (! empty($indirectSuperiors)) {
                foreach ($indirectSuperiors as $superior) {
                    $this->line("    • {$superior['name']} (层级: {$superior['level']})");
                }
            }

            // 层级链
            $this->newLine();
            $this->info('🔗 上级层级链:');
            $hierarchyChain = $result->getHierarchyChain();
            if (! empty($hierarchyChain)) {
                foreach ($hierarchyChain as $index => $level) {
                    $this->line("  L{$level['level']}: {$level['name']} (ID: {$level['id']})");
                }
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

            if (! empty($debugInfo['execution_log'])) {
                $this->line('执行日志:');
                foreach ($debugInfo['execution_log'] as $log) {
                    $this->line("  • $log");
                }
            }

            if (! empty($debugInfo['handlers'])) {
                $this->line('处理器信息:');
                foreach ($debugInfo['handlers'] as $handler) {
                    $this->line("  • $handler");
                }
            }
        }
    }
}
