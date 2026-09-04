<?php

namespace Modules\Application\Hooks\Definitions;

use Modules\ABase\Hooks\Core\HookDefinition;
use Modules\Application\Hooks\Parameters\UserListHookParameter;
use Modules\Application\Hooks\Results\UserListResult;

/**
 * 用户列表Hook定义
 *
 * 根据用户类型获取对应的用户列表，支持分页和搜索
 * 模块可以根据用户类型提供相应的用户数据
 */
class UserListHookDefinition extends HookDefinition
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
    public string $description = '根据用户类型获取用户列表，支持分页和搜索';

    /**
     * 是否为单处理器Hook
     *
     * 单处理器Hook在第一个处理器返回有效用户列表后停止执行
     */
    public bool $is_single_processor = true;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->parameter_class = UserListHookParameter::class;
        $this->return_class = UserListResult::class;

        parent::__construct();
    }
}
