<?php

namespace Modules\Application\Console;

use DLaravel\Commands\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Application\Services\JobRunService;

/**
 * 清理job_runs表命令
 *
 * 用于清理过期的队列运行记录，保持数据库性能
 * 默认保留5天内的记录，删除更早的记录
 */
class CleanJobRunsCommand extends Command
{
    /**
     * 命令名称
     *
     * @var string
     */
    protected $signature = 'application:clean-job-runs
                            {--days=5 : 保留多少天的记录，默认5天}
                            {--batch-size=1000 : 每批处理的数量，默认1000}
                            {--dry-run : 仅检查不执行实际删除操作}
                            {--force : 强制执行，跳过确认}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '清理job_runs表中的过期记录，保留指定天数内的数据';

    /**
     * 执行命令
     *
     * @return void
     */
    public function handleRun(): void
    {
        $this->info('开始清理job_runs表过期记录...');

        // 获取命令选项
        $days = (int) $this->option('days');
        $batchSize = (int) $this->option('batch-size');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        // 验证参数
        if ($days < 1) {
            $this->error('保留天数必须大于0');

            return;
        }

        if ($batchSize < 100 || $batchSize > 10000) {
            $this->error('批处理大小必须在100-10000之间');

            return;
        }

        // 计算截止时间（保留指定天数内的记录）
        $cutoffTime = Carbon::now()->subDays($days)->timestamp;
        $cutoffDate = Carbon::createFromTimestamp($cutoffTime)->format('Y-m-d H:i:s');

        $this->info("保留天数: {$days}天");
        $this->info("截止时间: {$cutoffDate}");
        $this->info("批处理大小: {$batchSize}");

        // 统计需要清理的记录数
        $totalCount = JobRunService::getCountBeforeTime($cutoffTime);

        if ($totalCount === 0) {
            $this->info('没有需要清理的记录');

            return;
        }

        $this->info("发现 {$totalCount} 条需要清理的记录");

        if ($dryRun) {
            $this->warn('预演模式：不会执行实际删除操作');
            $this->showCleanupPreview($cutoffTime);

            return;
        }

        // 确认操作
        if (! $force && ! $this->confirm("确定要删除 {$totalCount} 条记录吗？")) {
            $this->info('操作已取消');

            return;
        }

        // 执行清理
        $deletedCount = $this->performCleanup($cutoffTime, $batchSize);

        $this->info("清理完成，共删除 {$deletedCount} 条记录");

        // 记录操作日志
        Log::info('job_runs表清理完成', [
            'command' => 'system:clean-job-runs',
            'days' => $days,
            'cutoff_time' => $cutoffDate,
            'deleted_count' => $deletedCount,
            'batch_size' => $batchSize,
        ]);
    }

    /**
     * 显示清理预览信息
     */
    protected function showCleanupPreview(int $cutoffTime): void
    {
        $this->info('清理预览：');

        // 按状态统计
        $statusStats = JobRunService::getStatusStatsBeforeTime($cutoffTime);

        $this->table(['状态', '记录数'], $statusStats->map(function ($item) {
            return [$item->status ?: '未知', $item->count];
        })->toArray());

        // 按队列统计
        $queueStats = JobRunService::getQueueStatsBeforeTime($cutoffTime, 10);

        $this->info('按队列统计（前10）：');
        $this->table(['队列名称', '记录数'], $queueStats->map(function ($item) {
            return [$item->queue ?: 'default', $item->count];
        })->toArray());

        // 时间范围统计
        $oldestRecord = JobRunService::getOldestRecordBeforeTime($cutoffTime);

        if ($oldestRecord) {
            $oldestDate = Carbon::createFromTimestamp($oldestRecord->created_at)->format('Y-m-d H:i:s');
            $this->info("最早记录时间: {$oldestDate}");
        }
    }

    /**
     * 执行清理操作
     *
     * @return int 删除的记录数
     */
    protected function performCleanup(int $cutoffTime, int $batchSize): int
    {
        $totalDeleted = 0;
        $progressBar = $this->output->createProgressBar();
        $progressBar->setFormat('清理进度: %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');

        do {
            DB::beginTransaction();
            // 分批删除记录（通过Service层）
            $deleted = JobRunService::deleteBatchBeforeTime($cutoffTime, $batchSize);

            $totalDeleted += $deleted;
            $progressBar->advance($deleted);

            DB::commit();

            // 避免长时间占用数据库连接
            if ($deleted > 0) {
                usleep(100000); // 休息0.1秒
            }

        } while ($deleted > 0);

        $progressBar->finish();
        $this->newLine();

        return $totalDeleted;
    }
}
