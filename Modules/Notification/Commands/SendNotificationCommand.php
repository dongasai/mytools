<?php

declare(strict_types=1);

namespace Modules\Notification\Commands;

use Illuminate\Console\Command;
use Modules\User1\Models\User1;
use Modules\Notification\Services\ModuleNotificationService;
use Modules\Notification\Models\NotificationLog;

/**
 * 通知发送测试命令.
 *
 * 测试单个和批量通知发送功能。
 */
class SendNotificationCommand extends Command
{
    /**
     * 命令名称.
     *
     * @var string
     */
    protected $signature = 'notification:send
        {action : 测试动作 (single|batch|stats)}
        {--channel=database : 通知渠道 (mail|sms|push|database)}
        {--type=WelcomeNotification : 通知类型}
        {--user-id=* : 用户ID (单个发送时指定)}
        {--count=1 : 批量发送数量}
        {--force : 强制执行不询问确认}';

    /**
     * 命令描述.
     *
     * @var string
     */
    protected $description = '测试通知发送功能';

    /**
     * 执行命令.
     */
    public function handle(): int
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'single':
                return $this->testSingleSend();
            case 'batch':
                return $this->testBatchSend();
            case 'stats':
                return $this->showStatistics();
            default:
                $this->error('未知的测试动作: ' . $action);
                $this->info('可用动作: single, batch, stats');
                return self::FAILURE;
        }
    }

    /**
     * 测试单个通知发送.
     */
    protected function testSingleSend(): int
    {
        $channel = $this->option('channel');
        $type = $this->option('type');
        $userId = $this->option('user-id');

        $this->info('=== 测试单个通知发送 ===');
        $this->info('渠道: ' . $channel);
        $this->info('类型: ' . $type);

        // 获取或创建测试用户
        $user = $this->getTestUser($userId);

        if (!$user) {
            $this->error('用户不存在');
            return self::FAILURE;
        }

        $this->info('用户: ID=' . $user->id);

        // 准备测试数据
        $data = [
            'username' => '测试用户' . $user->id,
            'message' => '这是一条测试通知消息',
            'timestamp' => now()->toDateTimeString(),
        ];

        $this->info('发送数据: ' . json_encode($data, JSON_UNESCAPED_UNICODE));

        // 确认执行
        if (!$this->option('force') && !$this->confirm('确认发送测试通知？')) {
            $this->info('已取消');
            return self::SUCCESS;
        }

        // 发送通知（推送到队列）
        $log = ModuleNotificationService::send($type, $channel, $user, $data);

        $this->info('✓ 通知已推送到队列');
        $this->info('日志ID: ' . $log->id);
        $this->info('初始状态: ' . $log->status);
        $this->info('创建时间: ' . $log->created_at);

        // 等待队列处理并检查最终状态
        $finalStatus = $this->waitForQueueProcessing($log->id);

        if ($finalStatus === 'sent') {
            $this->info('');
            $this->info('✅ 通知发送成功！');
            $log->refresh();
            $this->displayLogDetails($log);
            return self::SUCCESS;
        } elseif ($finalStatus === 'failed') {
            $this->error('');
            $this->error('❌ 通知发送失败！');
            $log->refresh();
            $this->error('错误信息: ' . $log->error_message);
            $this->displayFailureHelp($log);
            return self::FAILURE;
        } else {
            $this->warn('');
            $this->warn('⚠️  通知处理超时（10秒）');
            $this->warn('当前状态: ' . $finalStatus);
            $this->info('');
            $this->info('可能原因:');
            $this->info('- 队列 worker 未启动');
            $this->info('- 队列任务执行时间过长');
            $this->info('');
            $this->info('处理建议:');
            $this->info('- 启动队列 worker: php artisan queue:work');
            $this->info('- 检查队列状态: php artisan queue:listen');
            $log->refresh();
            $this->displayLogDetails($log);
            return self::FAILURE;
        }
    }

    /**
     * 测试批量通知发送.
     */
    protected function testBatchSend(): int
    {
        $channel = $this->option('channel');
        $type = $this->option('type');
        $count = (int) $this->option('count');

        $this->info('=== 测试批量通知发送 ===');
        $this->info('渠道: ' . $channel);
        $this->info('类型: ' . $type);
        $this->info('数量: ' . $count);

        // 创建测试用户
        $users = $this->createTestUsers($count);

        $this->info('创建用户: ' . count($users) . ' 个');

        // 准备测试数据
        $data = [
            'username' => '批量测试用户',
            'message' => '这是一条批量测试通知',
            'batch_id' => uniqid('batch_', true),
            'timestamp' => now()->toDateTimeString(),
        ];

        $this->info('发送数据: ' . json_encode($data, JSON_UNESCAPED_UNICODE));

        // 确认执行
        if (!$this->option('force') && !$this->confirm('确认批量发送测试通知？')) {
            $this->info('已取消');
            return self::SUCCESS;
        }

        // 批量发送（推送到队列）
        $logs = ModuleNotificationService::sendBatch($type, $channel, $users, $data);

        $this->info('✓ 通知已推送到队列');
        $this->info('创建日志: ' . count($logs) . ' 条');

        foreach ($logs as $log) {
            $this->info('- ID=' . $log->id . ', status=' . $log->status . ', user_id=' . $log->notifiable_id);
        }

        // 等待队列处理
        $this->info('');
        $this->info('等待队列处理...');

        try {
            // 执行队列任务（批量）
            $this->info('执行队列任务...');
            $exitCode = $this->callSilent('queue:work', [
                '--once' => false,
                '--stop-when-empty' => true,
                '--tries' => 1,
                '--timeout' => 30,
            ]);

            if ($exitCode === 0) {
                $this->info('✓ 队列任务执行完成');
            } else {
                $this->warn('队列任务执行异常 (exit code: ' . $exitCode . ')');
            }
        } catch (\Exception $e) {
            $this->warn('队列执行异常: ' . $e->getMessage());
        }

        // 检查批量状态
        $this->info('');
        $this->info('检查批量状态...');

        $successCount = 0;
        $failedCount = 0;
        $pendingCount = 0;

        foreach ($logs as $log) {
            $log->refresh();
            if ($log->status === 'sent') {
                $successCount++;
            } elseif ($log->status === 'failed') {
                $failedCount++;
            } else {
                $pendingCount++;
            }
        }

        // 显示批量结果
        $this->info('');
        $this->info('批量发送结果:');
        $this->info('- 成功: ' . $successCount . ' 条');
        $this->info('- 失败: ' . $failedCount . ' 条');
        $this->info('- 待处理: ' . $pendingCount . ' 条');

        if ($failedCount > 0) {
            $this->error('');
            $this->error('❌ 有 ' . $failedCount . ' 条通知发送失败');
            $this->info('');
            $this->info('查看失败详情:');
            foreach ($logs as $log) {
                if ($log->status === 'failed') {
                    $this->info('- ID=' . $log->id . ', error=' . $log->error_message);
                }
            }
            return self::FAILURE;
        }

        if ($pendingCount > 0) {
            $this->warn('');
            $this->warn('⚠️  有 ' . $pendingCount . ' 条通知未处理完成');
            $this->info('');
            $this->info('建议启动队列 worker: php artisan queue:work');
            return self::FAILURE;
        }

        $this->info('');
        $this->info('✅ 所有通知发送成功！');
        return self::SUCCESS;
    }

    /**
     * 显示统计信息.
     */
    protected function showStatistics(): int
    {
        $this->info('=== 通知统计信息 ===');

        // 获取统计
        $stats = ModuleNotificationService::getStatistics();

        $this->info('通知状态统计:');
        $total = 0;
        foreach ($stats as $status => $count) {
            $this->info('- ' . $status . ': ' . $count . ' 条');
            $total += $count;
        }
        $this->info('总计: ' . $total . ' 条');

        // 按渠道统计
        $channelStats = NotificationLog::selectRaw('channel, count(*) as count')
            ->groupBy('channel')
            ->pluck('count', 'channel')
            ->toArray();

        $this->info('按渠道统计:');
        foreach ($channelStats as $channel => $count) {
            $this->info('- ' . $channel . ': ' . $count . ' 条');
        }

        // 按通知类型统计
        $typeStats = NotificationLog::selectRaw('notification_type, count(*) as count')
            ->groupBy('notification_type')
            ->pluck('count', 'notification_type')
            ->toArray();

        $this->info('按类型统计:');
        foreach ($typeStats as $type => $count) {
            $this->info('- ' . $type . ': ' . $count . ' 条');
        }

        // 最近的通知
        $recent = NotificationLog::orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'notification_type', 'channel', 'status', 'created_at']);

        $this->info('最近5条通知:');
        foreach ($recent as $log) {
            $this->info('- ID=' . $log->id . ', type=' . $log->notification_type . ', channel=' . $log->channel . ', status=' . $log->status . ', time=' . $log->created_at);
        }

        return self::SUCCESS;
    }

    /**
     * 获取测试用户.
     */
    protected function getTestUser(?array $userIds): ?User1
    {
        if ($userIds && count($userIds) > 0) {
            return User1::find($userIds[0]);
        }

        // 查找第一个用户
        $user = User1::first();

        if (!$user) {
            // 创建测试用户
            $user = User1::create([
                'email' => 'test-notification@example.com',
                'password' => bcrypt('password'),
            ]);
            $this->info('创建测试用户: ID=' . $user->id);
        }

        return $user;
    }

    /**
     * 创建多个测试用户.
     */
    protected function createTestUsers(int $count): array
    {
        $users = [];

        for ($i = 0; $i < $count; $i++) {
            $user = User1::create([
                'email' => 'test-notification-' . uniqid() . '@example.com',
                'password' => bcrypt('password'),
            ]);
            $users[] = $user;
        }

        return $users;
    }

    /**
     * 显示日志详情.
     */
    protected function displayLogDetails(NotificationLog $log): void
    {
        $this->info('--- 日志详情 ---');
        $this->info('ID: ' . $log->id);
        $this->info('类型: ' . $log->notification_type);
        $this->info('渠道: ' . $log->channel);
        $this->info('接收对象: ' . $log->notifiable_type . '#' . $log->notifiable_id);
        $this->info('状态: ' . $log->status);
        $this->info('重试次数: ' . $log->retry_count);
        $this->info('发送时间: ' . ($log->sent_at ?: '(未发送)'));
        $this->info('错误信息: ' . ($log->error_message ?: '(无)'));
        $this->info('创建时间: ' . $log->created_at);
        $this->info('数据: ' . json_encode($log->data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        $this->info('');
    }

    /**
     * 等待队列处理并返回最终状态.
     *
     * @param int $logId 日志ID
     * @param int $timeout 超时时间（秒）
     * @return string 最终状态
     */
    protected function waitForQueueProcessing(int $logId, int $timeout = 10): string
    {
        $this->info('');
        $this->info('等待队列处理...');

        $startTime = time();

        // 尝试执行队列任务
        try {
            // 启动一次性队列 worker
            $this->info('执行队列任务...');
            $exitCode = $this->callSilent('queue:work', [
                '--once' => true,
                '--tries' => 1,
                '--timeout' => $timeout,
            ]);

            if ($exitCode === 0) {
                $this->info('✓ 队列任务执行完成');
            } else {
                $this->warn('队列任务执行失败 (exit code: ' . $exitCode . ')');
            }
        } catch (\Exception $e) {
            $this->warn('队列执行异常: ' . $e->getMessage());
        }

        // 等待并轮询状态
        $this->info('检查通知状态...');
        while (time() - $startTime < $timeout) {
            $log = NotificationLog::find($logId);

            if (!$log) {
                $this->error('日志不存在: ID=' . $logId);
                return 'not_found';
            }

            // 检查是否已完成（非 pending/sending）
            if ($log->status !== 'pending' && $log->status !== 'sending') {
                return $log->status;
            }

            // 等待1秒后再次检查
            sleep(1);
        }

        // 超时，返回当前状态
        $log = NotificationLog::find($logId);
        return $log ? $log->status : 'timeout';
    }

    /**
     * 显示失败处理建议.
     */
    protected function displayFailureHelp(NotificationLog $log): void
    {
        $this->info('');
        $this->info('失败原因分析:');
        $this->info('- 渠道发送器未实现（Phase 5 功能）');
        $this->info('- 当前 sendByChannel() 方法返回 false');
        $this->info('');
        $this->info('处理建议:');
        $this->info('1. 查看日志详情:');
        $this->info('   php artisan notification:status --log-id=' . $log->id);
        $this->info('');
        $this->info('2. 查看队列日志:');
        $this->info('   tail -f storage/logs/laravel.log');
        $this->info('');
        $this->info('3. 实现渠道发送器（如果需要真实发送）:');
        $this->info('   - 编辑: Modules/Notification/Services/NotificationService.php');
        $this->info('   - 方法: sendByChannel()');
        $this->info('   - 实现: mail/sms/push/database 渠道发送逻辑');
    }
}