<?php

declare(strict_types=1);

namespace Modules\Application\Console;

use DLaravel\Commands\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Modules\Application\Jobs\UpdateContinuousTimesJob;
use Modules\Application\Models\ContinuousTimes;

/**
 * 队列测试命令
 *
 * 用于测试队列系统功能，发送UpdateContinuousTimesJob到队列并监控执行结果
 *
 * 测试流程：
 * 1. 获取固定测试记录（stype='queue_test', sid=1）的当前值
 * 2. 发送Job到队列
 * 3. 轮询检查值是否增加
 *
 * php artisan application:queue-test
 */
class TestQueueCommand extends Command
{
    /**
     * 命令名称
     *
     * @var string
     */
    protected $signature = 'application:queue-test
                            {--timeout=30 : 轮询超时时间（秒）}
                            {--interval=1 : 轮询间隔（秒）}
                            {--queue=default : 队列名称}
                            {--skip-monitor : 跳过监控（发送后立即退出）}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '测试队列功能：发送UpdateContinuousTimesJob到队列并监控执行结果';

    /**
     * 命令启动时间
     *
     * @var float
     */
    protected float $startTime;

    /**
     * 固定的测试键名
     */
    protected const TEST_STYPE = 'queue_test';
    protected const TEST_SID = 1;

    /**
     * 执行命令
     *
     * @return void
     */
    public function handleRun(): void
    {
        $this->startTime = microtime(true);
        $this->info('========================================');
        $this->info('        队列功能测试工具');
        $this->info('========================================');
        $this->newLine();

        // 获取参数
        $timeout = (int) $this->option('timeout');
        $interval = (int) $this->option('interval');
        $queueName = $this->option('queue');
        $skipMonitor = $this->option('skip-monitor');

        // 显示测试配置
        $this->showTestConfig($timeout, $interval, $queueName, $skipMonitor);

        // 步骤1: 获取当前记录值
        $this->info('步骤1: 获取当前测试记录值');
        $this->info('------------------------------');
        $initialNumber = $this->getCurrentNumber();

        if ($initialNumber === null) {
            $this->error('无法获取测试记录，退出测试');
            return;
        }

        $this->info("当前 number 值: {$initialNumber}");
        $this->newLine();

        // 步骤2: 发送Job到队列
        $this->info('步骤2: 发送Job到队列');
        $this->info('------------------------------');
        $jobDispatched = $this->dispatchJob($queueName);

        if (!$jobDispatched) {
            $this->error('Job发送失败，退出测试');
            return;
        }

        // 如果跳过监控，直接返回
        if ($skipMonitor) {
            $this->info('已跳过监控模式，Job已发送到队列');
            $this->showSummary($initialNumber, null, '跳过监控');
            return;
        }

        // 步骤3: 轮询检查结果
        $finalNumber = $this->pollForResult($initialNumber, $timeout, $interval);

        // 显示测试结果
        $this->showSummary($initialNumber, $finalNumber, $finalNumber !== null ? '成功' : '超时/失败');
    }

    /**
     * 显示测试配置
     *
     * @param int $timeout 超时时间
     * @param int $interval 轮询间隔
     * @param string $queueName 队列名称
     * @param bool $skipMonitor 是否跳过监控
     * @return void
     */
    protected function showTestConfig(int $timeout, int $interval, string $queueName, bool $skipMonitor): void
    {
        $this->info('测试配置:');
        $this->table(['配置项', '值'], [
            ['测试键', self::TEST_STYPE . ':' . self::TEST_SID],
            ['超时时间', $timeout . '秒'],
            ['轮询间隔', $interval . '秒'],
            ['队列名称', $queueName],
            ['监控模式', $skipMonitor ? '跳过' : '启用'],
        ]);
        $this->newLine();
    }

    /**
     * 获取当前测试记录的 number 值
     *
     * @return int|null 返回当前值，失败返回null
     */
    protected function getCurrentNumber(): ?int
    {
        $record = ContinuousTimes::query()
            ->where('stype', self::TEST_STYPE)
            ->where('sid', self::TEST_SID)
            ->first();

        if ($record === null) {
            // 记录不存在，返回 0
            return 0;
        }

        return (int) $record->number;
    }

    /**
     * 发送Job到队列
     *
     * @param string $queueName 队列名称
     * @return bool 是否发送成功
     */
    protected function dispatchJob(string $queueName): bool
    {
        $dispatchStartTime = microtime(true);

        $job = new UpdateContinuousTimesJob();
        $job->onQueue($queueName);

        // 获取队列配置信息
        $queueConnection = config('queue.default');
        $this->info("队列连接: {$queueConnection}");
        $this->info("队列名称: {$queueName}");

        // 发送Job
        $jobId = Queue::push($job);
        $dispatchEndTime = microtime(true);
        $dispatchDuration = round(($dispatchEndTime - $dispatchStartTime) * 1000, 2);

        if ($jobId === null) {
            $this->error('Job发送失败: Queue::push返回null');
            Log::error('TestQueueCommand: Job发送失败', [
                'queue' => $queueName,
            ]);

            return false;
        }

        $this->info("Job发送成功: ID={$jobId}");
        $this->info("发送耗时: {$dispatchDuration}ms");

        // 记录日志
        Log::info('TestQueueCommand: Job已发送到队列', [
            'queue_job_id' => $jobId,
            'queue' => $queueName,
            'dispatch_duration_ms' => $dispatchDuration,
        ]);

        $this->newLine();

        return true;
    }

    /**
     * 轮询检查结果
     *
     * @param int $initialNumber 初始值
     * @param int $timeout 超时时间
     * @param int $interval 轮询间隔
     * @return int|null 最终值，超时返回null
     */
    protected function pollForResult(int $initialNumber, int $timeout, int $interval): ?int
    {
        $this->info('步骤3: 轮询检查结果');
        $this->info('------------------------------');
        $expectedNumber = $initialNumber + 1;
        $this->info("期望 number 从 {$initialNumber} 变为 {$expectedNumber}");
        $this->newLine();

        $maxAttempts = (int) ceil($timeout / $interval);
        $currentAttempt = 0;

        while ($currentAttempt < $maxAttempts) {
            $currentAttempt++;
            $elapsedTime = round(microtime(true) - $this->startTime, 2);

            // 显示进度
            $this->output->write("\r轮询 {$currentAttempt}/{$maxAttempts} | 已等待 {$elapsedTime}s | ");

            // 获取当前值
            $currentNumber = $this->getCurrentNumber();

            if ($currentNumber === null) {
                $this->output->writeln('');
                $this->warn('无法获取当前值');
                return null;
            }

            // 检查是否更新成功
            if ($currentNumber >= $expectedNumber) {
                $this->output->writeln('');
                $this->info('Job执行成功！');
                return $currentNumber;
            }

            // 等待下一次轮询
            sleep($interval);
        }

        $this->output->writeln('');
        $this->warn('轮询超时，Job可能尚未执行完成');

        // 返回当前值
        return $this->getCurrentNumber();
    }

    /**
     * 显示测试摘要
     *
     * @param int $initialNumber 初始值
     * @param int|null $finalNumber 最终值
     * @param string $status 测试状态
     * @return void
     */
    protected function showSummary(int $initialNumber, ?int $finalNumber, string $status): void
    {
        $this->newLine();
        $this->info('========================================');
        $this->info('           测试结果摘要');
        $this->info('========================================');

        $totalTime = round(microtime(true) - $this->startTime, 2);

        // 状态颜色
        $statusColor = match ($status) {
            '成功' => 'info',
            '跳过监控' => 'comment',
            default => 'error',
        };

        $this->$statusColor("状态: {$status}");
        $this->newLine();

        // 基本信息
        $this->info('基本信息:');
        $this->table(['项目', '值'], [
            ['测试键', self::TEST_STYPE . ':' . self::TEST_SID],
            ['总耗时', $totalTime . '秒'],
            ['初始 number', $initialNumber],
            ['最终 number', $finalNumber ?? 'N/A'],
            ['变化值', $finalNumber !== null ? ($finalNumber - $initialNumber) : 'N/A'],
        ]);

        // 如果测试完成，显示验证结果
        if ($finalNumber !== null && $status !== '跳过监控') {
            $this->newLine();
            $actualIncrement = $finalNumber - $initialNumber;

            if ($actualIncrement >= 1) {
                $this->info('验证: number 增加值符合预期（+1或更多）');
            } else {
                $this->error("验证失败: number 增加了 {$actualIncrement}，预期至少增加 1");
            }
        }

        $this->newLine();
        $this->info('========================================');

        // 记录日志
        Log::info('TestQueueCommand: 测试完成', [
            'test_key' => self::TEST_STYPE . ':' . self::TEST_SID,
            'status' => $status,
            'total_time_seconds' => $totalTime,
            'initial_number' => $initialNumber,
            'final_number' => $finalNumber,
        ]);
    }
}