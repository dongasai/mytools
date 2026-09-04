<?php

namespace DLaravel\Commands;

use Illuminate\Console\Command;

/**
 * DLaravel 示例命令
 *
 * 用于演示 DLaravel 命令系统的示例命令
 */
class ExampleCommand extends Command
{
    protected $signature = 'ucore:example';

    protected $description = 'DLaravel示例命令';

    public function handle()
    {
        $this->info('DLaravel命令执行成功');
    }
}
