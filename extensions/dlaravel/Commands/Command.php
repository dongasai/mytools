<?php

namespace DLaravel\Commands;

use DLaravel\Commands\Command\CommandFace;
use DLaravel\Commands\Command\CommandTrait;
use DLaravel\Queue\Helper;

/**
 * 命令基类
 */
abstract class Command extends \Illuminate\Console\Command implements CommandFace
{
    use CommandTrait;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Helper::add_log('run', 'Console', static::class, []);
        $start = microtime(true);
        try {
            $this->handleRun();
        } catch (\Exception $exception) {
            $this->callException($exception);
            $this->output->error($exception->getMessage());
        }
        $diff = microtime(true) - $start;
        Helper::add_log('run-end', 'Console', static::class, [
            'runtime' => $diff,
        ], '', $diff);
    }
}
