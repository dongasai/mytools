<?php

declare(strict_types=1);

namespace Modules\Application\Console;

use DLaravel\Commands\Command;
use Modules\ABase\Hooks\Management\Hooks;
use Modules\Application\Hooks\Definitions\UserDetailHookDefinition;
use Modules\Application\Hooks\Parameters\UserDetailHookParameter;
use Modules\Application\Hooks\Handlers\UserDetailHookHandler;

/**
 * 用户详情Hook控制台命令
 *
 * 用于测试和调试用户详情Hook功能
 */
class UserDetailHookCommand extends Command
{
    /**
     * 命令名称
     */
    protected $signature = 'application:hook:user-detail
                            {--user-id= : 用户ID (必需)}
                            {--include-roles= : 是否包含角色信息 (true, false)}
                            {--include-permissions= : 是否包含权限信息 (true, false)}
                            {--debug : 显示调试信息}';

    /**
     * 命令描述
     */
    protected $description = '应用用户详情Hook并返回结果';

    /**
     * 执行命令
     */
    public function handleRun(): void
    {
        $this->info('👤 执行用户详情Hook...');

        // 获取命令选项
        $userId = $this->option('user-id');
        $includeRoles = $this->option('include-roles') === 'true';
        $includePermissions = $this->option('include-permissions') === 'true';
        $debug = $this->option('debug');

        if (!$userId) {
            $this->error('❌ 用户ID是必需的参数，请使用 --user-id= 选项指定');
            return;
        }

        if ($debug) {
            Hooks::enableDebug();
        }

        // 创建Hook参数
        $parameter = UserDetailHookParameter::create(
            userId: (int) $userId,
            includeRoles: $includeRoles,
            includePermissions: $includePermissions
        );

        $this->line('📋 Hook参数:');
        $this->table(['参数', '值'], [
            ['用户ID', $parameter->user_id],
            ['包含角色信息', $parameter->include_roles ? 'true' : 'false'],
            ['包含权限信息', $parameter->include_permissions ? 'true' : 'false'],
        ]);

        // 注意：Hook处理器应在ServiceProvider中统一注册，这里仅用于测试
        // 检查处理器是否已注册
        $handlers = Hooks::getHandlers(UserDetailHookDefinition::class);
        if (empty($handlers)) {
            $this->warn('⚠️ 用户详情Hook处理器未注册，请检查ServiceProvider配置');
            $this->warn('预期处理器: UserDetailHookHandler');
        }

        // 应用Hook
        $this->line('⚡ 正在应用Hook...');
        $result = Hooks::apply(UserDetailHookDefinition::class, $parameter);

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
            ['用户信息', $result->hasUser() ? '已获取' : '未获取'],
        ]);

        // 错误信息
        if ($result->hasErrors()) {
            $this->newLine();
            $this->error('⚠️ 错误信息:');
            foreach ($result->getErrors() as $error) {
                $this->line("  • $error");
            }
        }

        // 用户详情
        if ($result->hasUser()) {
            $user = $result->getUser();
            $this->newLine();
            $this->info('👤 用户详情:');

            $this->table(['字段', '值'], [
                ['ID', $user->id],
                ['姓名', $user->name],
                ['邮箱', $user->email],
                ['用户类型', $user->type],
                ['状态', $user->is_active ? '活跃' : '非活跃'],
                ['创建时间', $user->created_at ?? '-'],
                ['更新时间', $user->updated_at ?? '-'],
            ]);

            // 角色信息
            if ($result->hasRoles()) {
                $this->newLine();
                $this->info('🎭 角色信息:');
                $roles = $result->getRoles();
                foreach ($roles as $role) {
                    $this->line("  • {$role->name} ({$role->description})");
                }
            }

            // 权限信息
            if ($result->hasPermissions()) {
                $this->newLine();
                $this->info('🔐 权限信息:');
                $permissions = $result->getPermissions();
                foreach ($permissions as $permission) {
                    $this->line("  • {$permission->name} - {$permission->description}");
                }
            }

            // 扩展信息
            if ($result->hasExtendedInfo()) {
                $this->newLine();
                $this->info('📋 扩展信息:');
                $extendedInfo = $result->getExtendedInfo();
                $this->line(json_encode($extendedInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
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
