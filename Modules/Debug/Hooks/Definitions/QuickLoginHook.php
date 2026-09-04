<?php

declare(strict_types=1);

namespace Modules\Debug\Hooks\Definitions;

use Modules\ABase\Hooks\Core\HookDefinition;
use Modules\ABase\Hooks\Parameters\MapHookParameter;
use Modules\Enterprise\Hooks\Results\EnterpriseDebugHookResult;

/**
 * 快速登录Hook定义
 *
 * Debug模块请求Enterprise模块执行快速登录（通过uid直接登录，无需密码）
 */
class QuickLoginHook extends HookDefinition
{
    /**
     * 参数类名
     */
    public readonly string $parameter_class;

    /**
     * 返回值类名
     */
    public readonly string $return_class;

    /**
     * Hook描述
     */
    public string $description = 'Debug模块请求快速登录，通过uid直接获取token';

    public function __construct()
    {
        $this->parameter_class = MapHookParameter::class;
        $this->return_class = EnterpriseDebugHookResult::class;
        parent::__construct();
    }
}
