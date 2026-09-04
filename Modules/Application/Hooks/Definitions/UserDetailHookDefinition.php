<?php

namespace Modules\Application\Hooks\Definitions;

use Modules\ABase\Hooks\Core\HookDefinition;
use Modules\Application\Hooks\Parameters\UserDetailHookParameter;
use Modules\Application\Hooks\Results\UserDetailResult;
use Modules\Application\Dtos\User;

/**
 * 用户详情Hook定义
 *
 * 根据用户ID获取用户的详细信息，包括基本资料、角色权限等
 * 模块可以根据用户ID提供用户的详细信息
 */
class UserDetailHookDefinition extends HookDefinition
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
    public string $description = '根据用户ID获取用户详细信息';

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->parameter_class = UserDetailHookParameter::class;
        $this->return_class = UserDetailResult::class;

        parent::__construct();
    }
}
