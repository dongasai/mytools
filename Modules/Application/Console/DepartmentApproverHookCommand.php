<?php

declare(strict_types=1);

namespace Modules\Application\Console;

use DLaravel\Commands\Command;
use Modules\ABase\Hooks\Management\Hooks;
use Modules\Application\Hooks\Definitions\DepartmentApproverHookDefinition;
use Modules\Application\Hooks\Parameters\DepartmentApproverHookParameter;

/**
 * 部门审批人Hook控制台命令
 *
 * 用于测试和调试部门审批人Hook功能
 */
class DepartmentApproverHookCommand extends Command
{
    /**
     * 命令名称
     */
    protected $signature = 'application:hook:department-approver
                            {--source=admin : 源模块 (admin, shop, workflow)}
                            {--user-id= : 用户ID}
                            {--department-id= : 部门ID}
                            {--org-type= : 组织类型}
                            {--approval-type= : 审批类型 (leave, expense, purchase)}
                            {--level= : 审批级别}
                            {--include-backup= : 是否包含备用审批人 (true, false)}
                            {--debug : 显示调试信息}';

    /**
     * 命令描述
     */
    protected $description = '应用部门审批人Hook并返回结果';

    /**
     * 执行命令
     */
    public function handleRun(): void
    {
        $this->info('✅ 执行部门审批人Hook...');

        // 获取命令选项
        $source = $this->option('source') ?? 'admin';
        $userId = $this->option('user-id') ? (int) $this->option('user-id') : null;
        $departmentId = $this->option('department-id') ? (int) $this->option('department-id') : null;
        $orgType = $this->option('org-type');
        $approvalType = $this->option('approval-type');
        $level = $this->option('level') ? (int) $this->option('level') : null;
        $includeBackup = $this->option('include-backup') === 'true';
        $debug = $this->option('debug');

        if ($debug) {
            Hooks::enableDebug();
        }

        // 创建Hook参数
        $parameter = DepartmentApproverHookParameter::create(
            source: $source,
            userId: $userId,
            departmentId: $departmentId,
            orgType: $orgType,
            approvalType: $approvalType,
            level: $level,
            includeBackup: $includeBackup
        );

        $this->line('📋 Hook参数:');
        $this->table(['参数', '值'], [
            ['源模块', $parameter->source],
            ['用户ID', $parameter->user_id ?? 'null'],
            ['部门ID', $parameter->department_id ?? 'null'],
            ['组织类型', $parameter->org_type ?? 'null'],
            ['审批类型', $parameter->approval_type ?? 'null'],
            ['审批级别', $parameter->level ?? 'null'],
            ['包含备用审批人', $parameter->include_backup ? 'true' : 'false'],
        ]);

        // 应用Hook
        $this->line('⚡ 正在应用Hook...');
        $result = Hooks::apply(DepartmentApproverHookDefinition::class, $parameter);

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
            ['审批人数量', $result->getCount()],
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

        // 审批人列表
        if ($result->isNotEmpty()) {
            $this->newLine();
            $this->info('✅ 部门审批人列表:');

            $approvers = $result->getApprovers();
            $tableData = [];

            foreach ($approvers as $approver) {
                $tableData[] = [
                    'ID' => $approver['id'],
                    '姓名' => $approver['name'],
                    '部门' => $approver['department_id'],
                    '审批类型' => $approver['approval_type'],
                    '级别' => $approver['level'],
                    '审批人类型' => $approver['approver_type'],
                    '状态' => $approver['is_active'] ? '活跃' : '非活跃',
                    '权限' => implode(', ', $approver['permissions']),
                ];
            }

            $this->table([
                'ID',
                '姓名',
                '部门ID',
                '审批类型',
                '级别',
                '审批人类型',
                '状态',
                '权限'
            ], $tableData);

            // 按审批类型分组
            $this->newLine();
            $this->info('📋 按审批类型分组:');

            $types = ['leave', 'expense', 'purchase', 'travel', 'other'];
            foreach ($types as $type) {
                $typeApprovers = $result->getApproversByType($type);
                if (!empty($typeApprovers)) {
                    $this->line("  📄 $type: " . count($typeApprovers) . "个审批人");
                    foreach ($typeApprovers as $approver) {
                        $this->line("    • {$approver['name']} (级别: {$approver['level']})");
                    }
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

            if (!empty($debugInfo['execution_log'])) {
                $this->line('执行日志:');
                foreach ($debugInfo['execution_log'] as $log) {
                    $this->line("  • $log");
                }
            }

            if (!empty($debugInfo['handlers'])) {
                $this->line('处理器信息:');
                foreach ($debugInfo['handlers'] as $handler) {
                    $this->line("  • $handler");
                }
            }
        }
    }
}
