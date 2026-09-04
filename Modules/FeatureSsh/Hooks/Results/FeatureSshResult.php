<?php

namespace Modules\FeatureSsh\Hooks\Results;

use Modules\ABase\Hooks\Results\HookResult;

/**
 * FeatureSsh 钩子结果
 */
class FeatureSshResult extends HookResult
{
    /**
     * 结果数据
     *
     * @var mixed
     */
    protected mixed $result = null;
}