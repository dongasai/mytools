<?php

namespace DLaravel\Commands\Command;

use DLaravel\Helper\Helper;
use DLaravel\Helper\Logger;
use Illuminate\Console\Command;

/**
 * 按秒执行的命令基类
 */
abstract class CommandSecond extends Command implements CommandSecondFace
{
    /**
     * 等待秒数(最后几秒不执行)
     *
     * @var int
     */
    protected $waitSecond = 2;

    /**
     * 间隔时长
     *
     * @var int
     */
    protected $sleepSecond = 1;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Helper::add_log('run-s', 'Console', static::class, []);

        foreach (range(1, 60) as $a) {
            $s = date('s');
            if ($s >= (60 - $this->waitSecond)) {
                // 最后几秒 不执行
                break;
            }
            try {
                $this->handleSecond($s);
            } catch (\Exception $exception) {
                Logger::exception('CommandSecond'.static::class, $exception);
                echo $exception->getMessage();
                echo $exception->getTraceAsString();
            }
            sleep($this->sleepSecond);
        }
        Helper::add_log('run-s-end', 'Console', static::class, []);

        return Command::SUCCESS;
    }
}
