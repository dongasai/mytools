<?php

declare(strict_types=1);

namespace Modules\Notification\Commands;

use Illuminate\Console\Command;
use Modules\Notification\Models\NotificationLog;
use Modules\Notification\Logics\NotificationLogic;
use Modules\Notification\Enums\NOTIFICATION_STATUS;

/**
 * 状态测试命令.
 *
 * 测试通知状态更新功能。
 */
class StatusNotificationCommand extends Command
{
    /**
     * 命令名称.
     *
     * @var string
     */
    protected $signature = 'notification:status
        {--log-id=* : 日志ID (可选，不指定则自动查找pending日志)}
        {--force : 强制执行不询问确认}';

    /**
     * 命令描述.
     *
     * @var string
     */
    protected $description = '测试通知状态更新功能';

    /**
     * 执行命令.
     */
    public function handle(): int
    {
        $logIds = $this->option('log-id');

        $this->info('=== 测试状态更新功能 ===');

        if (empty($logIds)) {
            // 查找最近的待发送日志
            $log = NotificationLog::where('status', NOTIFICATION_STATUS::PENDING->value)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$log) {
                $this->error('没有找到待发送的通知日志');
                $this->info('请先创建测试通知:');
                $this->info('php artisan notification:send single --force');
                return self::FAILURE;
            }

            $logIds = [$log->id];
        }

        foreach ($logIds as $logId) {
            $log = NotificationLog::find($logId);

            if (!$log) {
                $this->error('日志不存在: ID=' . $logId);
                continue;
            }

            $this->info('测试日志: ID=' . $log->id . ', 当前状态=' . $log->status);

            // 确认执行
            if (!$this->option('force') && !$this->confirm('确认执行状态更新测试？')) {
                $this->info('已跳过');
                continue;
            }

            $this->testStatusUpdates($log);
        }

        return self::SUCCESS;
    }

    /**
     * 测试状态更新流程.
     */
    protected function testStatusUpdates(NotificationLog $log): void
    {
        // 测试状态更新流程
        $this->info('1. 标记为发送中...');
        NotificationLogic::markAsSending($log);
        $this->info('✓ 状态更新: sending');
        $this->displayLogDetails($log);

        $this->info('2. 标记为已发送...');
        NotificationLogic::markAsSent($log);
        $this->info('✓ 状态更新: sent');
        $this->info('✓ 发送时间: ' . $log->sent_at);
        $this->displayLogDetails($log);

        $this->info('3. 增加重试次数...');
        NotificationLogic::incrementRetry($log);
        $this->info('✓ 重试次数: ' . $log->retry_count);
        $this->displayLogDetails($log);

        $this->info('4. 标记为失败...');
        NotificationLogic::markAsFailed($log, '测试错误: 模拟发送失败');
        $this->info('✓ 状态更新: failed');
        $this->info('✓ 错误信息: ' . $log->error_message);
        $this->displayLogDetails($log);
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
}