<?php

namespace Modules\Application\Hooks\Definitions;

use Modules\ABase\Hooks\Core\HookDefinition;
use Modules\Application\Hooks\Parameters\UserTypeHookParameter;
use Modules\Application\Hooks\Results\UserTypeResult;
use Modules\Application\Dtos\UserType;

/**
 * 用户类型Hook定义
 *
 * 用于获取系统中可用的用户类型，如系统用户、管理员、商城用户等
 * 其他模块可以通过此Hook提供用户类型数据
 */
class UserTypeHookDefinition extends HookDefinition
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
    public string $description = '获取应用系统中可用的用户类型';

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->parameter_class = UserTypeHookParameter::class;
        $this->return_class = UserTypeResult::class;

        parent::__construct();
    }
}
