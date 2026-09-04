<?php

declare(strict_types=1);

namespace Modules\ABase\Hooks\Core;

/**
 * Hook参数接口
 *
 * 定义所有Hook参数必须实现的方法
 */
interface HookParameterInterface
{
    /**
     * 转换为数组
     */
    public function toArray(): array;
}
