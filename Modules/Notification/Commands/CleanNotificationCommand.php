<?php

declare(strict_types=1);

namespace Modules\Notification\Commands;

use Illuminate\Console\Command;
use Modules\Notification\Models\NotificationLog;

/**
 * 通知清理命令.
 *
 * 清理积压的通知日志数据，支持按状态、渠道、类型清理。
 */
class CleanNotificationCommand extends Command
{
    /**
     * 命令名称.
     *
     * @var string
     */
    protected $signature = 'notification:clean
        {--status=* : 清理指定状态 (pending|sending|sent|failed)}
        {--channel=* : 清理指定渠道 (mail|database|sms|push)}
        {--type=* : 清理指定类型}
        {--older-than= : 清理N天前的数据 (例如: 30 表示30天前)}
        {--limit=1000 : 单次清理最大记录数，防止误删大量数据}
        {--mark-failed : 将pending标记为failed而不是删除}
        {--dry-run : 预览模式，不实际执行}
        {--force : 强制执行不询问确认}';

    /**
     * 命令描述.
     *
     * @var string
     */
    protected $description = '清理积压的通知日志数据';

    /**
     * 执行命令.
     */
    public function handle(): int
    {
        $this->info('=== 通知日志清理 ===');

        // 获取limit参数
        $limit = (int) $this->option('limit');

        // 构建查询
        $query = NotificationLog::query();

        // 按状态筛选
        $statuses = $this->option('status');
        if ($statuses) {
            $query->whereIn('status', $statuses);
            $this->info('筛选状态: ' . implode(', ', $statuses));
        }

        // 按渠道筛选
        $channels = $this->option('channel');
        if ($channels) {
            $query->whereIn('channel', $channels);
            $this->info('筛选渠道: ' . implode(', ', $channels));
        }

        // 按类型筛选
        $types = $this->option('type');
        if ($types) {
            $query->whereIn('notification_type', $types);
            $this->info('筛选类型: ' . implode(', ', $types));
        }

        // 按时间筛选
        $olderThan = $this->option('older-than');
        if ($olderThan) {
            $days = (int) $olderThan;
            $query->where('created_at', '<', now()->subDays($days));
            $this->info('筛选时间: ' . $days . ' 天前');
        }

        // 显示limit设置
        $this->info('单次清理限制: ' . $limit . ' 条');

        // 获取统计
        $totalCount = $query->count();
        $this->info('符合条件的总记录数: ' . $totalCount . ' 条');

        if ($totalCount === 0) {
            $this->info('没有需要清理的数据');
            return self::SUCCESS;
        }

        // 应用limit
        $query->limit($limit);
        $actualCount = $query->count();
        $this->info('本次清理记录数: ' . $actualCount . ' 条');

        // 预览模式
        if ($this->option('dry-run')) {
            $this->info('');
            $this->info('预览模式 - 以下数据将被处理:');
            $logs = $query->limit(10)->get(['id', 'notification_type', 'channel', 'status', 'created_at']);
            foreach ($logs as $log) {
                $this->info('- ID=' . $log->id . ', type=' . $log->notification_type . ', channel=' . $log->channel . ', status=' . $log->status . ', time=' . $log->created_at);
            }
            if ($actualCount > 10) {
                $this->info('... 还有 ' . ($actualCount - 10) . ' 条记录');
            }
            return self::SUCCESS;
        }

        // 确认执行
        if (!$this->option('force') && !$this->confirm('确认清理 ' . $actualCount . ' 条记录？')) {
            $this->info('已取消');
            return self::SUCCESS;
        }

        // 执行清理
        $markFailed = $this->option('mark-failed');

        if ($markFailed) {
            // 标记为失败
            $updated = $query->update([
                'status' => 'failed',
                'error_message' => '手动清理标记为失败',
            ]);
            $this->info('✓ 已将 ' . $updated . ' 条记录标记为 failed');

            if ($totalCount > $limit) {
                $this->warn('提示: 还有 ' . ($totalCount - $limit) . ' 条记录未清理');
                $this->info('可以多次运行此命令或调整 --limit 参数');
            }
        } else {
            // 删除记录（软删除）
            $deleted = $query->delete();
            $this->info('✓ 已删除 ' . $deleted . ' 条记录（软删除）');

            if ($totalCount > $limit) {
                $this->warn('提示: 还有 ' . ($totalCount - $limit) . ' 条记录未清理');
                $this->info('可以多次运行此命令或调整 --limit 参数');
            }
        }

        // 显示清理后的统计
        $this->info('');
        $this->info('清理后统计:');
        $stats = NotificationLog::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        foreach ($stats as $status => $count) {
            $this->info('- ' . $status . ': ' . $count . ' 条');
        }

        return self::SUCCESS;
    }
}