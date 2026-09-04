<?php

namespace Modules\FeatureSsh\Hooks\Definitions;

use Modules\ABase\Hooks\Hook;

/**
 * FeatureSsh 模块钩子定义
 *
 * 示例钩子，用于模块间通信
 */
class FeatureSshHook extends Hook
{
    /**
     * 钩子类型
     *
     * @var string
     */
    protected string $hookType = 'featuressh';

    /**
     * 钩子描述
     *
     * @var string
     */
    protected string $description = 'FeatureSsh 模块钩子';
}