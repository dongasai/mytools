<?php

namespace DLaravel\Queue;

/**
 * 队列任务接口
 */
interface QueueJobInterface
{
    /**
     * 实际运行
     */
    public function run(): bool;

    /**
     * 获取任务数据
     *
     * 最好返回索引数组,依次传入构造函数
     *
     * @return mixed
     */
    public function payload();
}
