<?php

namespace Modules\Application\Validator;

use DLaravel\Validator\Validator;

/**
 * 连续次数验证器
 *
 * 用于检查用户在指定时间内的连续操作次数限制
 */
abstract class ContinuousTimes extends Validator
{
    /**
     * 用户ID
     *
     * @var int
     */
    protected int $user_id = 0;

    /**
     * 操作类型标识
     *
     * @var string
     */
    protected string $type = '';

    /**
     * 操作ID（如手机号、IP等）
     *
     * @var string
     */
    protected string $sid = '';

    /**
     * 允许的最大次数
     *
     * @var int
     */
    protected int $times = 3;

    /**
     * 时间间隔（秒），超过此时间后次数重置
     *
     * @var int
     */
    protected int $diff = 120;

    /**
     * 验证是否超过次数限制
     *
     * @param mixed $value 验证值
     * @param array $data 数据
     * @return bool true=通过验证，false=超过限制
     */
    public function validate(mixed $value, array $data): bool
    {
        $this->sid = $value;

        return $this->checkValidate();
    }

    /**
     * 检查是否验证通过
     *
     * @return bool
     */
    protected function checkValidate(): bool
    {
        return \Modules\Application\Services\ContinuousTimes::check(
            $this->user_id,
            $this->type,
            $this->sid,
            $this->times
        );
    }

    /**
     * 增加计数
     *
     * @return int 当前的连续次数
     */
    protected function add(): int
    {
        return \Modules\Application\Services\ContinuousTimes::add(
            $this->user_id,
            $this->type,
            $this->sid,
            1,
            $this->diff
        );
    }

    /**
     * 获取当前次数
     *
     * @return int
     */
    protected function getTimes(): int
    {
        $model = \Modules\Application\Models\ContinuousTimes::query()
            ->where('user_id', $this->user_id)
            ->where('stype', $this->type)
            ->where('sid', $this->sid)
            ->first();

        return $model ? $model->number : 0;
    }
}
