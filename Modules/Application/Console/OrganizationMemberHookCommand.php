<?php

declare(strict_types=1);

namespace Modules\Application\Console;

use DLaravel\Commands\Command;
use Modules\ABase\Hooks\Management\Hooks;
use Modules\Application\Hooks\Definitions\OrganizationMemberHookDefinition;
use Modules\Application\Hooks\Parameters\OrganizationMemberHookParameter;

/**
 * 组织成员Hook控制台命令
 *
 * 用于测试和调试组织成员Hook功能
 */
class OrganizationMemberHookCommand extends Command
{
    /**
     * 命令名称
     */
    protected $signature = 'application:hook:organization-member
                            {--source=application : 源模块 (admin, shop, workflow)}
                            {--user-id= : 用户ID}
                            {--org-type= : 组织类型ID}
                            {--org-id= : 组织ID}
                            {--role= : 角色过滤}
                            {--active=1 : 是否只获取活跃成员}
                            {--debug : 显示调试信息}';

    /**
     * 命令描述
     */
    protected $description = '应用组织成员Hook并返回结果';

    /**
     * 执行命令
     */
    public function handleRun(): void
    {
        $this->info('👥 执行组织成员Hook...');

        // 获取命令选项
        $source = $this->option('source') ?? 'admin';
        $userId = $this->option('user-id') ? (int) $this->option('user-id') : null;
        $orgType = $this->option('org-type');
        $orgId = $this->option('org-id') ? (int) $this->option('org-id') : null;
        $role = $this->option('role');
        $active = $this->option('active') === 'true' || $this->option('active') === '1';
        $debug = $this->option('debug');

        if ($debug) {
            Hooks::enableDebug();
        }

        // 创建Hook参数
        $parameter = OrganizationMemberHookParameter::create(
            source: $source,
            userId: $userId,
            orgType: $orgType,
            orgId: $orgId,
            role: $role,
            active: $active
        );

        $this->line('📋 Hook参数:');
        $this->table(['参数', '值'], [
            ['源模块', $parameter->source],
            ['用户ID', $parameter->user_id ?? 'null'],
            ['组织类型', $parameter->org_type ?? 'null'],
            ['组织ID', $parameter->org_id ?? 'null'],
            ['角色过滤', $parameter->role ?? 'null'],
            ['只获取活跃成员', $parameter->active ? 'true' : 'false'],
        ]);

        // 应用Hook
        $this->line('⚡ 正在应用Hook...');
        $result = Hooks::apply(OrganizationMemberHookDefinition::class, $parameter);

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
            ['成员数量', $result->getCount()],
            ['总数量', $result->total_count],
            '是否为空' => $result->isEmpty() ? '是' : '否',
        ]);

        // 错误信息
        if ($result->hasErrors()) {
            $this->newLine();
            $this->error('⚠️ 错误信息:');
            foreach ($result->getErrors() as $error) {
                $this->line("  • $error");
            }
        }

        // 成员列表
        if ($result->isNotEmpty()) {
            $this->newLine();
            $this->info('👥 组织成员列表:');

            $members = $result->getMembers();
            $tableData = [];

            foreach ($members as $member) {
                $tableData[] = [
                    'ID' => $member->id,
                    '姓名' => $member->name,
                    '邮箱' => $member->email,
                    '组织' => $member->organization_id,
                    '角色' => $member->role,
                    '状态' => $member->is_active ? '活跃' : '非活跃',
                ];
            }

            // 限制显示数量，避免输出过长
            if (count($tableData) > 20) {
                $this->info('显示前20个成员（总共' . count($tableData) . '个）:');
                $tableData = array_slice($tableData, 0, 20);
            }

            $this->table(['ID', '姓名', '邮箱', '组织ID', '角色', '状态'], $tableData);

            // 分页信息
            $pagination = $result->getPaginationInfo();
            if (! empty($pagination)) {
                $this->newLine();
                $this->info('📄 分页信息:');
                $this->line(json_encode($pagination, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
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
