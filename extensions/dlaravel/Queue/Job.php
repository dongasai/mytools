<?php

namespace DLaravel\Queue;

use DLaravel\Dto\Queue;
use DLaravel\Queue\QueueJob;

class Job extends QueueJob
{
    public function __construct(public Queue $arg) {}

    public function run(): bool
    {
        dump($this->arg, [$this->arg->runClass, $this->arg->runMethod]);
        $res = call_user_func([$this->arg->runClass, $this->arg->runMethod], $this->arg->runParam);
        // dump($res);

        return true;
    }

    public function payload()
    {

        return $this->arg;
    }
}
