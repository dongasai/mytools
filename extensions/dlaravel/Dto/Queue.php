<?php

namespace DLaravel\Dto;

class Queue
{

    /**
     * 创建时间
     * @var int
     */
    public int $create_ts;

    /**
     * 延迟时间
     * @var int
     */
    public int $delay_ts;


    /**
     * 运行类
     * @var string
     */
    public string $runClass;

    /**
     * 运行方法
     * @var string
     */

    public string $runMethod;

    /**
     * 运行参数
     * @var string
     */
    public $runParam;
}
