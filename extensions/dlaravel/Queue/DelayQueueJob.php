<?php

namespace DLaravel\Queue;

use DLaravel\Dto\Queue;
use DLaravel\Helper\Redis;
use DLaravel\Queue\QueueJob;

class DelayQueueJob extends QueueJob
{
    public function __construct(public Queue $arg) {}

    public function run(): bool
    {
        $key = Redis::getkey([$this->arg->runClass, $this->arg->runMethod], $this->arg->runParam);
        /**
         * @var \Redis $a
         */
        $a = \Illuminate\Support\Facades\Redis::client();
        $a->del($key);

        //        dump($this->arg,[$this->arg->runClass,$this->arg->runMethod]);
        $res = call_user_func([$this->arg->runClass, $this->arg->runMethod], $this->arg->runParam);

        //        dump($res);
        return true;
    }

    public function payload()
    {

        return $this->arg;
    }
}
