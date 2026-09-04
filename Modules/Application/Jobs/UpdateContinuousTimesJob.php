<?php

declare(strict_types=1);

namespace Modules\Application\Jobs;

use DLaravel\Queue\QueueJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Application\Models\ContinuousTimes;

/**
 * 更新连续次数记录的队列任务
 *
 * 用于测试队列系统功能，更新固定的测试记录
 * 固定使用 stype='queue_test', sid=1 的记录
 */
class UpdateContinuousTimesJob extends QueueJob
{
    /**
     * 最大重试次数
     *
     * @var int
     */
    public $tries = 3;

    /**
     * 超时时间（秒）
     *
     * @var int
     */
    public $timeout = 60;

    /**
     * 创建任务实例
     */
    public function __construct()
    {
        parent::__construct([]);
    }

    /**
     * 执行队列任务
     *
     * @return bool
     */
    public function run(): bool
    {
        $startTime = microtime(true);

        Log::info('UpdateContinuousTimesJob 开始执行', [
            'attempt' => $this->attempts(),
        ]);

        $result = false;

        DB::transaction(function () use (&$result) {
            // 固定使用 stype='queue_test', sid=1 的记录
            $record = ContinuousTimes::query()
                ->where('stype', 'queue_test')
                ->where('sid', 1)
                ->first();

            if ($record === null) {
                // 如果记录不存在，创建新记录
                $record = ContinuousTimes::create([
                    'user_id' => 0,
                    'stype' => 'queue_test',
                    'sid' => 1,
                    'number' => 0,
                    'last_time' => 0,
                ]);

                Log::info('UpdateContinuousTimesJob 创建测试记录', [
                    'record_id' => $record->id,
                ]);
            }

            $oldNumber = $record->number;
            $oldLastTime = $record->last_time;

            // 更新记录：number +1，更新 last_time
            $record->number += 1;
            $record->last_time = time();
            $record->save();

            Log::info('UpdateContinuousTimesJob 记录已更新', [
                'record_id' => $record->id,
                'old_number' => $oldNumber,
                'new_number' => $record->number,
                'old_last_time' => $oldLastTime,
                'new_last_time' => $record->last_time,
            ]);

            $result = true;
        });

        $duration = round((microtime(true) - $startTime) * 1000, 2);

        Log::info('UpdateContinuousTimesJob 执行完成', [
            'result' => $result ? 'success' : 'failed',
            'duration_ms' => $duration,
        ]);

        return $result;
    }

    /**
     * 获取任务数据
     *
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return [
            'test_key' => 'queue_test',
        ];
    }
}