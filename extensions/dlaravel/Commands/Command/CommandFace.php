<?php

namespace DLaravel\Commands\Command;

/**
 * 命令接口
 *
 * 定义命令执行的基本方法
 */
interface CommandFace
{
    /**
     * 执行命令的具体逻辑
     *
     * @return void
     */
    public function handleRun();
}
