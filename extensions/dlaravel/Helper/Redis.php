<?php

namespace DLaravel\Helper;

use DLaravel\Dto\Queue;
use DLaravel\Helper\Job\DelayArrayJob;
use DLaravel\Helper\Job\DelayQueueJob;
use DLaravel\Queue\Job;

class Redis
{

    const E_KEY  = 'delay_queue';

    const E_KEY2 = 'delay_queue2';

    /**
     * 添加队列
     *
     * @param array $callback
     * @param $runParam
     * @param int $delay
     * @return int
     */
    static public function addQueue($callback, $runParam, $delay = 3): int
    {
        if (!is_callable($callback)) {
            throw new \Exception('callback is not callable');
        }
        $key = self::E_KEY . $callback[0] . $callback[1] . md5(serialize($runParam));

        /**
         * @var \Redis $a
         */
        $a = \Illuminate\Support\Facades\Redis::client();
        if ($a->exists($key)) {
            return 0;
        }
        $delay = max(2, $delay);
        $a->setex($key, max(1, $delay - 1), 1);

        $q            = new Queue();
        $q->create_ts = time();
        $q->delay_ts  = $delay;
        $q->runClass  = $callback[0];
        $q->runMethod = $callback[1];
        $q->runParam  = $runParam;

        Job::dispatch($q)->delay($delay);

        return 1;
    }


    /**
     * 增强版本
     *
     * @param $callback
     * @param $runParam
     * @param $delay
     * @return int
     * @throws \Exception
     */
    static public function addQueue2($callback, $runParam, $delay = 3): int
    {

        if (!is_callable($callback)) {
            throw new \Exception('callback is not callable');
        }
        $key = self::getkey($callback, $runParam);
        /**
         * @var \Redis $a
         */
        $a = \Illuminate\Support\Facades\Redis::client();
        if ($a->exists($key)) {
            return 0;
        }
        $delay = max(2, $delay);
        $a->setex($key, max(100, $delay * 3), 1);

        $q            = new Queue();
        $q->create_ts = time();
        $q->delay_ts  = $delay;
        $q->runClass  = $callback[0];
        $q->runMethod = $callback[1];
        $q->runParam  = $runParam;

        DelayQueueJob::dispatch($q)->delay($delay);

//        Job::dispatch($q)->delay($delay);

        return 1;
    }

    /**
     * 获取缓存Key
     *
     * @param $callback
     * @param $runParam
     * @return string
     */
    public static function getkey($callback, $runParam)
    {
        return self::E_KEY2 . $callback[0] . $callback[1] . md5(serialize($runParam));

    }

    /**
     * 增强版本-Array参数
     *
     * @param $callback
     * @param $runParam
     * @param $delay
     * @return int
     * @throws \Exception
     */
    static public function addQueueArr($callback, $runParam, $delay = 3): int
    {

        if (!is_callable($callback)) {
            throw new \Exception('callback is not callable');
        }
        $key = self::getkey($callback, $runParam);
        /**
         * @var \Redis $a
         */
        $a = \Illuminate\Support\Facades\Redis::client();
        if ($a->exists($key)) {
            return 0;
        }
        $delay = max(2, $delay);
        $a->setex($key, max(100, $delay * 3), 1);

        $q            = new Queue();
        $q->create_ts = time();
        $q->delay_ts  = $delay;
        $q->runClass  = $callback[0];
        $q->runMethod = $callback[1];
        $q->runParam  = $runParam;

        DelayArrayJob::dispatch($q)->delay($delay);

//        Job::dispatch($q)->delay($delay);

        return 1;
    }

}
