<?php

namespace Modules\Application\DcatAdmin\Metrics;

use DLaravel\Helper\Cache;
use Illuminate\Support\Facades\DB;

/**
 * 系统报警
 * Class Alarm
 */
class Alarm extends \Modules\DcatAdmin\DcatAdmin\Metrics\Examples\ListDataColor
{
    protected $title = '运行情况';

    /**
     * 初始化卡片内容
     */
    protected function init()
    {
        parent::init();

        $this->title('运行情况');
        $this->height(200);
        $this->chart = null;

    }

    /**
     * 获取监控数据
     */
    protected function getData(): array
    {
        return Cache::cacheCall([__CLASS__, __FUNCTION__, 2], function () {
            $now = time();
            $data = [];

            // 检查最近的任务执行记录
            $lastJob = $this->getLastConsoleJob();
            if ($lastJob) {
                $va = [
                    'title' => 'Cron分钟任务',
                    'value' => date('H:i:s', $lastJob->created_at),
                    'type' => self::TYPE_OK,
                ];
                if ($now - $lastJob->created_at > 300) {
                    $va['type'] = self::TYPE_ERROR;
                } elseif ($now - $lastJob->created_at > 60) {
                    $va['type'] = self::TYPE_WARNING;
                }
                $data[] = $va;
            }

            // 检查指导价格计算任务
            $lastGuidanceJob = $this->getLastGuidanceJob();
            if ($lastGuidanceJob) {
                $va = [
                    'title' => '计算指导价格',
                    'value' => date('m-d H:i:s', $lastGuidanceJob->created_at),
                    'type' => self::TYPE_OK,
                ];
                if ($now - $lastGuidanceJob->created_at > (3600 * 24)) {
                    $va['type'] = self::TYPE_ERROR;
                }
                $data[] = $va;
            }

            return $data;
        }, [], 10);
    }

    /**
     * 获取最近的Console队列任务
     */
    private function getLastConsoleJob(): ?object
    {
        // 使用原生查询获取最近的Console队列任务
        $job = DB::table('jobs')
            ->where('queue', 'Console')
            ->orderBy('id', 'desc')
            ->first();

        return $job;
    }

    /**
     * 获取最近的指导价格计算任务
     */
    private function getLastGuidanceJob(): ?object
    {
        // 查找payload中包含GuidanceJisuan类的任务
        $job = DB::table('jobs')
            ->where('queue', 'Console')
            ->where('payload', 'like', '%GuidanceJisuan%')
            ->orderBy('id', 'desc')
            ->first();

        return $job;
    }
}
