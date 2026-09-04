<?php

declare(strict_types=1);

namespace Modules\Application\Console;

use DLaravel\Commands\Command;
use Modules\ABase\Hooks\Management\Hooks;
use Modules\Application\Hooks\Definitions\DepartmentLeaderHookDefinition;
use Modules\Application\Hooks\Parameters\DepartmentLeaderHookParameter;

/**
 * 部门负责人Hook控制台命令
 *
 * 用于测试和调试部门负责人Hook功能
 */
class DepartmentLeaderHookCommand extends Command
{
    /**
     * 命令名称
     */
    protected $signature = 'application:hook:department-leader
                            {--source=application : 源模块 (admin, shop, workflow)}
                            {--user-id= : 用户ID}
                            {--department-id= : 部门ID}
                            {--org-type= : 组织类型}
                            {--include-acting= : 是否包含代理负责人 (true, false)}
                            {--debug : 显示调试信息}';

    /**
     * 命令描述
     */
    protected $description = '应用部门负责人Hook并返回结果';

    /**
     * 执行命令
     */
    public function handleRun(): void
    {
        $this->info('👔 执行部门负责人Hook...');

        // 获取命令选项
        $source = $this->option('source') ?? 'admin';
        $userId = $this->option('user-id') ? (int) $this->option('user-id') : null;
        $departmentId = $this->option('department-id') ? (int) $this->option('department-id') : null;
        $orgType = $this->option('org-type');
        $includeActing = $this->option('include-acting') === 'true';
        $debug = $this->option('debug');

        if ($debug) {
            Hooks::enableDebug();
        }

        // 创建Hook参数
        $parameter = DepartmentLeaderHookParameter::create(
            source: $source,
            userId: $userId,
            departmentId: $departmentId,
            orgType: $orgType,
            includeActing: $includeActing
        );

        $this->line('📋 Hook参数:');
        $this->table(['参数', '值'], [
            ['源模块', $parameter->source],
            ['用户ID', $parameter->user_id ?? 'null'],
            ['部门ID', $parameter->department_id ?? 'null'],
            ['组织类型', $parameter->org_type ?? 'null'],
            ['包含代理负责人', $parameter->include_acting ? 'true' : 'false'],
        ]);

        // 应用Hook
        $this->line('⚡ 正在应用Hook...');
        $result = Hooks::apply(DepartmentLeaderHookDefinition::class, $parameter);

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
            ['负责人数量', $result->getCount()],
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

        // 负责人列表
        if ($result->isNotEmpty()) {
            $this->newLine();
            $this->info('👔 部门负责人列表:');

            $leaders = $result->getLeaders();
            $tableData = [];

            foreach ($leaders as $leader) {
                $tableData[] = [
                    'ID' => $leader['id'],
                    '姓名' => $leader['name'],
                    '部门' => $leader['department_id'],
                    '角色类型' => $leader['role_type'],
                    '负责人类型' => $leader['leader_type'],
                    '状态' => $leader['is_active'] ? '活跃' : '非活跃',
                    '生效时间' => $leader['effective_from'] ?? '-',
                    '到期时间' => $leader['effective_to'] ?? '-',
                ];
            }

            $this->table([
                'ID',
                '姓名',
                '部门ID',
                '角色类型',
                '负责人类型',
                '状态',
                '生效时间',
                '到期时间',
            ], $tableData);

            // 按类型分组显示
            $this->newLine();
            $this->info('📋 按负责人类型分组:');

            $formalLeaders = $result->getFormalLeaders();
            $actingLeaders = $result->getActingLeaders();
            $backupLeaders = $result->getBackupLeaders();

            $this->line('  🎯 正式负责人: ' . count($formalLeaders) . '个');
            if (! empty($formalLeaders)) {
                foreach ($formalLeaders as $leader) {
                    $this->line("    • {$leader['name']} (ID: {$leader['id']})");
                }
            }

            $this->line('  🔄 代理负责人: ' . count($actingLeaders) . '个');
            if (! empty($actingLeaders)) {
                foreach ($actingLeaders as $leader) {
                    $this->line("    • {$leader['name']} (ID: {$leader['id']})");
                }
            }

            $this->line('  🔙 备用负责人: ' . count($backupLeaders) . '个');
            if (! empty($backupLeaders)) {
                foreach ($backupLeaders as $leader) {
                    $this->line("    • {$leader['name']} (ID: {$leader['id']})");
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
