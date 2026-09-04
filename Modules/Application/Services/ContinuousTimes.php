<?php

declare(strict_types=1);

namespace Modules\Application\Services;

use Modules\Application\Models\ContinuousTimes as ContinuousTimesModel;

/**
 * 连续次数处理服务
 *
 * 提供连续次数记录的增删改查和测试辅助方法
 */
class ContinuousTimes
{
    /**
     * 增加计数
     *
     * @return int|mixed
     */
    public static function add($user_id, $stype, $sid, $times = 1, $diff = 120)
    {

        $old = ContinuousTimesModel::query()->where([
            'user_id' => $user_id,
            'stype' => $stype,
            'sid' => $sid,
        ])->first();
        if (! $old) {
            $old = new ContinuousTimesModel;
            $old->user_id = $user_id;
            $old->stype = $stype;
            $old->sid = $sid;
            $old->last_time = 0;
            $old->number = 0;

        }
        $old->diff = $diff;

        if (($old->last_time + $diff) < time()) {
            // 不连续了
            $old->number = 0;
        }
        $old->last_time = time();
        $old->number += $times;
        $old->save();

        //        dump($old->stype,$stype);
        return $old->number;

    }

    /**
     * 检查是否超过限制
     *
     * @return bool
     */
    public static function check($user_id, $stype, $sid, $times = 1)
    {

        /**
         * @var ContinuousTimesModel $old
         */
        $old = ContinuousTimesModel::query()->where([
            'user_id' => $user_id,
            'stype' => $stype,
            'sid' => $sid,
        ])->first();

        if (! $old) {

            return true;
        }
        $diff = $old->diff;
        if (($old->last_time + $diff) < time()) {
            // 不连续了
            $old->number = 0;
        }

        if ($old->number >= $times) {
            return false;
        }

        return true;
    }

    /**
     * 创建测试记录
     *
     * 用于队列测试，创建一个初始值为0的测试记录
     *
     * @param int $userId 用户ID，默认0
     * @param string $stype 类型，默认'test'
     * @param int $sid 对象ID，默认0
     * @return ContinuousTimesModel
     */
    public static function createTestRecord(int $userId = 0, string $stype = 'test', int $sid = 0): ContinuousTimesModel
    {
        $record = new ContinuousTimesModel;
        $record->user_id = $userId;
        $record->stype = $stype;
        $record->sid = $sid;
        $record->number = 0;
        $record->last_time = 0;
        $record->diff = 120;
        $record->save();

        return $record;
    }

    /**
     * 根据ID更新记录的number值
     *
     * @param int $recordId 记录ID
     * @param int $incrementValue 增加的值
     * @return bool 更新是否成功
     */
    public static function updateNumberById(int $recordId, int $incrementValue): bool
    {
        $record = ContinuousTimesModel::query()->where('id', $recordId)->first();

        if ($record === null) {
            return false;
        }

        $record->number += $incrementValue;
        $record->last_time = time();
        $record->save();

        return true;
    }

    /**
     * 根据ID获取记录状态
     *
     * @param int $recordId 记录ID
     * @return array|null 记录状态数组，不存在则返回null
     */
    public static function getStateById(int $recordId): ?array
    {
        $record = ContinuousTimesModel::query()->where('id', $recordId)->first();

        if ($record === null) {
            return null;
        }

        return [
            'id' => $record->id,
            'user_id' => $record->user_id,
            'stype' => $record->stype,
            'sid' => $record->sid,
            'number' => $record->number,
            'last_time' => $record->last_time,
            'diff' => $record->diff,
            'updated_at' => $record->updated_at?->toDateTimeString(),
        ];
    }
}
