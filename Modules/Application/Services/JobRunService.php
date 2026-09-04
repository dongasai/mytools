<?php

namespace Modules\Application\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * 队列任务运行记录服务
 *
 * 提供队列任务运行记录的管理和清理功能
 */
class JobRunService
{
    /**
     * 获取指定时间之前的记录数量
     *
     * @param int $cutoffTime 截止时间戳
     * @return int
     */
    public static function getCountBeforeTime(int $cutoffTime): int
    {
        return \DLaravel\Models\JobRun::where('created_at', '<', $cutoffTime)->count();
    }

    /**
     * 批量删除指定时间之前的记录
     *
     * @param int $cutoffTime 截止时间戳
     * @param int $batchSize 每批删除数量
     * @return int 删除的记录数
     */
    public static function deleteBatchBeforeTime(int $cutoffTime, int $batchSize): int
    {
        return \DLaravel\Models\JobRun::where('created_at', '<', $cutoffTime)
            ->limit($batchSize)
            ->delete();
    }

    /**
     * 获取按状态统计的清理记录信息
     *
     * @param int $cutoffTime 截止时间戳
     * @return Collection
     */
    public static function getStatusStatsBeforeTime(int $cutoffTime): Collection
    {
        return \DLaravel\Models\JobRun::where('created_at', '<', $cutoffTime)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();
    }

    /**
     * 获取按队列统计的清理记录信息
     *
     * @param int $cutoffTime 截止时间戳
     * @param int $limit 限制数量
     * @return Collection
     */
    public static function getQueueStatsBeforeTime(int $cutoffTime, int $limit = 10): Collection
    {
        return \DLaravel\Models\JobRun::where('created_at', '<', $cutoffTime)
            ->select('queue', DB::raw('count(*) as count'))
            ->groupBy('queue')
            ->orderBy('count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * 获取最早的清理记录
     *
     * @param int $cutoffTime 截止时间戳
     * @return \DLaravel\Models\JobRun|null
     */
    public static function getOldestRecordBeforeTime(int $cutoffTime): ?\DLaravel\Models\JobRun
    {
        return \DLaravel\Models\JobRun::where('created_at', '<', $cutoffTime)
            ->orderBy('created_at', 'asc')
            ->first();
    }
}