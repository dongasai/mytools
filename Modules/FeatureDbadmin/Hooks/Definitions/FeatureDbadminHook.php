<?php

namespace Modules\FeatureDbadmin\Hooks\Definitions;

use Modules\ABase\Hooks\Hook;

/**
 * FeatureDbadmin 模块钩子定义
 *
 * 示例钩子，用于模块间通信
 */
class FeatureDbadminHook extends Hook
{
    /**
     * 钩子类型
     *
     * @var string
     */
    protected string $hookType = 'featuredbadmin';

    /**
     * 钩子描述
     *
     * @var string
     */
    protected string $description = 'FeatureDbadmin 模块钩子';
}