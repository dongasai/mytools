<?php

namespace Modules\ABase\Support\DelayQueue\Job;

use Modules\ABase\Support\DelayQueue\Entity\Queue;
use Modules\ABase\Support\DelayQueue\Redis;
use DLaravel\Queue\QueueJob;

/**
 * 延迟队列任务
 *
 * 用于执行延迟队列中的任务，任务执行前清除Redis中的延迟记录
 */
class DelayQueueJob extends QueueJob
{
    /**
     * 队列参数
     */
    public function __construct(public Queue $arg) {}

    /**
     * 执行队列任务
     *
     * 清除Redis延迟记录并调用目标方法
     *
     * @return bool
     */
    public function run(): bool
    {
        $key = Redis::getkey([$this->arg->runClass, $this->arg->runMethod], $this->arg->runParam);
        /**
         * @var \Redis $a
         */
        $a = \Illuminate\Support\Facades\Redis::client();
        $a->del($key);

        $res = call_user_func([$this->arg->runClass, $this->arg->runMethod], $this->arg->runParam);

        return true;
    }

    /**
     * 获取任务载荷
     *
     * @return Queue
     */
    public function payload(): Queue
    {
        return $this->arg;
    }
}
