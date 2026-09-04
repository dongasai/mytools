<?php

namespace Modules\ABase\Support\DelayQueue\Entity;

class Queue
{
    /**
     * 创建时间
     */
    public int $create_ts;

    /**
     * 延迟时间
     */
    public int $delay_ts;

    /**
     * 运行类
     */
    public string $runClass;

    /**
     * 运行方法
     */
    public string $runMethod;

    /**
     * 运行参数
     *
     * @var string
     */
    public $runParam;
}
