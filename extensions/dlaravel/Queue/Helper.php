<?php

namespace DLaravel\Queue;

use DLaravel\Helper\Logger;
use DLaravel\Model\JobRun;

/**
 * 队列助手类
 *
 * 用于处理队列任务的日志记录
 */
class Helper
{
    /**
     * 添加队列任务日志
     *
     * @param  string  $status  任务状态
     * @param  string  $queue  队列名称
     * @param  string  $runclass  运行类名
     * @param  mixed  $payload  任务数据
     * @param  string  $desc  描述信息
     * @param  float  $runtime  运行时间
     */
    public static function add_log(
        string $status,
        string $queue,
        string $runclass,
        mixed $payload,
        string $desc = '',
        float $runtime = 0
    ): void {
        Logger::info("jon run $runclass  : $status - $desc");

        if (! is_string($payload)) {
            $payload = serialize($payload);
        }

        $model = new JobRun;
        $model->status = $status;
        $model->payload = $payload;
        $model->queue = $queue ?? '';
        $model->runclass = $runclass;
        $model->attempts = 0;
        $model->available_at = 0;
        $model->created_at = time();
        $model->desc = $desc;
        $model->runtime = round(max($runtime, 0.0001) * 1000, 5);
        $model->save();
    }
}
