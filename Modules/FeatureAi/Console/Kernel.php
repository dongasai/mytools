<?php

namespace Modules\FeatureAi\Console;

use Illuminate\Console\Scheduling\Schedule;
use Modules\FeatureAi\Commands\CleanTempFilesCommand;

/**
 * FeatureAi模块的Console Kernel
 *
 * 注册模块的定时任务
 */
class Kernel
{
    /**
     * 注册定时任务
     *
     * @param Schedule $schedule
     */
    public function schedule(Schedule $schedule): void
    {
        // 每天02:00清理临时文件（24小时过期）
        $schedule->command(CleanTempFilesCommand::class, ['--hours=24'])
            ->dailyAt('02:00')
            ->withoutOverlapping()
            ->onOneServer()
            ->appendOutputTo(storage_path('logs/feature-ai-temp-cleanup.log'));
    }
}