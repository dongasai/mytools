<?php

namespace Modules\ABase\Support\DelayQueue\Console;

use DLaravel\Console\Command;
use DLaravel\Console\CommandSecond;
use DLaravel\Helper\Logger;

/**
 *  系统清理
 */
class DelayQueueRun extends CommandSecond
{
    /**
     * 等待时间
     *
     * @var int
     */
    protected $waitSecond = 3;

    /**
     * 间隔时长
     *
     * @var int
     */
    protected $sleepSecond = 2;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app-delayqueue:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '延迟队列-执行模块';

    public function handleSecond($s)
    {

        Logger::info('');

        return true;
    }
}
