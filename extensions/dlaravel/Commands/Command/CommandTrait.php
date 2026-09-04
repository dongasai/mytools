<?php

namespace DLaravel\Commands\Command;

use DLaravel\Helper\Logger;

/**
 * 命令公共特性
 */
trait CommandTrait
{
    /**
     * 处理命令执行过程中的异常
     *
     * @param  \Exception  $exception  捕获到的异常
     * @return void
     */
    public function callException(\Exception $exception)
    {
        Logger::exception('error', $exception);
    }
}
