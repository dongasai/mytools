<?php

namespace DLaravel\Commands\Command;

/**
 * 按秒执行的命令接口
 */
interface CommandSecondFace
{
    /**
     * 每秒执行的具体逻辑
     *
     * @param  int  $s  当前秒数
     * @return void
     */
    public function handleSecond($s);
}
