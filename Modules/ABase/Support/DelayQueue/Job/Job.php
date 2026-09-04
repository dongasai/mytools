<?php

namespace Modules\ABase\Support\DelayQueue\Job;

use Modules\ABase\Support\DelayQueue\Entity\Queue;
use DLaravel\Queue\QueueJob;

class Job extends QueueJob
{
    public function __construct(public Queue $arg) {}

    public function run(): bool
    {
        $res = call_user_func([$this->arg->runClass, $this->arg->runMethod], $this->arg->runParam);

        return true;
    }

    public function payload()
    {

        return $this->arg;
    }
}
