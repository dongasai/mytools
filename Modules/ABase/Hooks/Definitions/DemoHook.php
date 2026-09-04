<?php

declare(strict_types=1);

namespace Modules\ABase\Hooks\Definitions;

use Modules\ABase\Hooks\Core\HookDefinition;
use Modules\ABase\Hooks\Parameters\DemoHookParameter;
use Modules\ABase\Hooks\Results\DemoHookResult;

/**
 * DemoHook定义
 *
 * 演示Hook系统的完整实现，展示参数传递、处理和结果返回
 */
class DemoHook extends HookDefinition
{
    /**
     * 参数类名（子类必须定义）
     * 必须继承自HookParameter
     */
    public readonly string $parameter_class;

    /**
     * 返回值类名（子类必须定义）
     * 必须继承自HookResult
     */
    public readonly string $return_class;

    public string $description = '演示Hook系统的参数处理和结果返回';

    public function __construct()
    {
        $this->parameter_class = DemoHookParameter::class;
        $this->return_class = DemoHookResult::class;
        parent::__construct();
    }
}
