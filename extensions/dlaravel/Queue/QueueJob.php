<?php

namespace DLaravel\Queue;

use DLaravel\Helper\Logger;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * 队列任务基类
 */
abstract class QueueJob implements QueueJobInterface, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $args = [];

    /**
     * 创建新的任务实例
     *
     * @return void
     */
    public function __construct($args = [])
    {
        //
        $this->args = $args;
    }

    /**
     * 任务可尝试的次数。
     *
     * @var int
     */
    public $tries = 1000;

    public function handle(): void
    {
        $start = microtime(true);
        Helper::add_log('handle', $this->job->getQueue(), static::class, $this->payload());
        $res = null;
        $diff = 0;
        try {
            $res = $this->run();
            $diff = microtime(true) - $start;
            Helper::add_log('runend-'.$res, $this->job->getQueue(), static::class, $this->payload(), '', $diff);
            if ($res) {
                $this->delete();
            }
        } catch (\Throwable $exception) {
            Logger::exception('job', $exception);
            $desc = '';
            $desc .= $exception->getMessage();
            $desc .= $exception->getTraceAsString();
            Helper::add_log('Throwable-'.get_class($exception), $this->job->getQueue(), static::class, $this->payload(), $desc);
            // 10 * n 秒后重试
            $this->release($this->attempts() * $this->attempts() * $this->attempts() * 2);
        }
        Helper::add_log('runend-'.$res, $this->job->getQueue(), static::class, $this->payload(), '', $diff);

    }

    public function failed(\Throwable $exception): void
    {
        $desc = '';
        $desc .= $exception->getMessage();
        $desc .= $exception->getTraceAsString();
        Helper::add_log('failed-'.get_class($exception), static::class, static::class, $this->payload(), $desc);

    }

    public function logInfo($name, $data = [])
    {
        Log::info(static::class.'-'.$name.' '.json_encode($data));
    }
}
