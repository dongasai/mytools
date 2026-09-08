<?php

namespace Modules\FeatureDbadmin\Hooks\Results;

use Modules\ABase\Hooks\Results\HookResult;

/**
 * FeatureDbadmin 钩子结果
 */
class FeatureDbadminResult extends HookResult
{
    /**
     * 结果数据
     *
     * @var mixed
     */
    protected mixed $result = null;
}