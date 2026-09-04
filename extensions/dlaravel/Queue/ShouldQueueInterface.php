<?php

namespace DLaravel\Queue;

/**
 * 队列事件监听器接口
 *
 * 为队列事件监听器定义统一的接口规范
 */
interface ShouldQueueInterface
{
    /**
     * 实际运行方法
     *
     * 队列事件监听器的核心执行方法
     *
     * @param  object  $event  事件对象
     * @return bool 返回true表示处理成功，false表示处理失败
     */
    public function run(object $event): bool;
}
