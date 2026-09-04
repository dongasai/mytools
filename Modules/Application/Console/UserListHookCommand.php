<?php

declare(strict_types=1);

namespace Modules\Application\Console;

use DLaravel\Commands\Command;
use Modules\ABase\Hooks\Management\Hooks;
use Modules\Application\Hooks\Definitions\UserListHookDefinition;
use Modules\Application\Hooks\Parameters\UserListHookParameter;
use Modules\Application\Hooks\Handlers\UserListHookHandler;

/**
 * 用户列表Hook控制台命令
 *
 * 用于测试和调试用户列表Hook功能
 */
class UserListHookCommand extends Command
{
    /**
     * 命令名称
     */
    protected $signature = 'application:hook:user-list
                            {--user-type=cc : 人员类型 (cc, approver, notifier)}
                            {--type= : 用户类型 (account, admin, shop)}
                            {--search= : 搜索关键词}
                            {--limit=50 : 限制数量}
                            {--offset=0 : 偏移量}
                            {--debug : 显示调试信息}';

    /**
     * 命令描述
     */
    protected $description = '应用用户列表Hook并返回结果';

    /**
     * 执行命令
     */
    public function handleRun(): void
    {
        $this->info('👥 执行用户列表Hook...');

        // 获取命令选项
        $userType = $this->option('user-type') ?? 'cc';
        $type = $this->option('type');
        $search = $this->option('search');
        $limit = $this->option('limit') ? (int) $this->option('limit') : 50;
        $offset = $this->option('offset') ? (int) $this->option('offset') : 0;
        $debug = $this->option('debug');

        if ($debug) {
            Hooks::enableDebug();
        }

        // 创建Hook参数
        $parameter = UserListHookParameter::create(
            user_type: \Modules\Workflow\Enums\USER_TYPE::from($userType),
            type: $type,
            search: $search,
            limit: $limit,
            offset: $offset
        );

        $this->line('📋 Hook参数:');
        $this->table(['参数', '值'], [
            ['人员类型', $parameter->user_type->value],
            ['用户类型', $parameter->type ?: 'null'],
            ['搜索关键词', $parameter->search ?: 'null'],
            ['限制数量', $parameter->limit],
            ['偏移量', $parameter->offset],
        ]);

        // 注意：Hook处理器应在ServiceProvider中统一注册，这里仅用于测试
        // 检查处理器是否已注册
        $handlers = Hooks::getHandlers(UserListHookDefinition::class);
        if (empty($handlers)) {
            $this->warn('⚠️ 用户列表Hook处理器未注册，请检查ServiceProvider配置');
            $this->warn('预期处理器: UserListHookHandler');
        }

        // 应用Hook
        $this->line('⚡ 正在应用Hook...');
        $result = Hooks::apply(UserListHookDefinition::class, $parameter);

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
            ['用户数量', $result->getCount()],
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

        // 用户列表
        if ($result->isNotEmpty()) {
            $this->newLine();
            $this->info('👥 用户列表:');

            $users = $result->getUsers();
            $tableData = [];

            foreach ($users as $user) {
                $tableData[] = [
                    'ID' => $user->id,
                    '姓名' => $user->name,
                    '邮箱' => $user->email,
                    '用户类型' => $user->type,
                    '状态' => $user->is_active ? '活跃' : '非活跃',
                ];
            }

            // 限制显示数量，避免输出过长
            if (count($tableData) > 20) {
                $this->info('显示前20个用户（总共' . count($tableData) . '个）:');
                $tableData = array_slice($tableData, 0, 20);
            }

            $this->table(['ID', '姓名', '邮箱', '用户类型', '状态'], $tableData);

            // 分页信息
            $pagination = $result->getPaginationInfo();
            if (!empty($pagination)) {
                $this->newLine();
                $this->info('📄 分页信息:');
                $this->line(json_encode($pagination, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
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
