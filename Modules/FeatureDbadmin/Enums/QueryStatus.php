<?php

namespace Modules\FeatureDbadmin\Enums;

/**
 * 查询状态枚举
 *
 * 标识 SQL 查询的执行状态，记录查询执行结果
 */
enum QueryStatus: string
{
    /** 执行成功 - 查询已完成且无错误 */
    case SUCCESS = 'SUCCESS';

    /** 执行失败 - 查询执行过程中发生错误 */
    case FAILED = 'FAILED';

    /**
     * 获取中文标签
     *
     * @return string 状态的中文描述
     */
    public function label(): string
    {
        return match ($this) {
            self::SUCCESS => '执行成功',
            self::FAILED => '执行失败',
        };
    }

    /**
     * 是否执行成功
     *
     * @return bool 查询是否成功执行
     */
    public function isSuccess(): bool
    {
        return $this === self::SUCCESS;
    }
}
