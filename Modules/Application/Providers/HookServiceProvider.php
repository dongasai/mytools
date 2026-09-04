<?php

declare(strict_types=1);

namespace Modules\Application\Providers;

use Modules\ABase\Support\HookServiceProvider as BaseHookServiceProvider;
use Modules\Application\Hooks\Definitions\DepartmentLeaderHookDefinition;
use Modules\Application\Hooks\Definitions\OrganizationMemberHookDefinition;
use Modules\Application\Hooks\Definitions\OrganizationTypeHookDefinition;
use Modules\Application\Hooks\Definitions\SuperiorHookDefinition;
use Modules\Application\Hooks\Definitions\UserDetailHookDefinition;
use Modules\Application\Hooks\Definitions\UserListHookDefinition;
use Modules\Application\Hooks\Definitions\UserTypeHookDefinition;

/**
 * Application 模块 Hook 服务提供者
 *
 * 负责注册 Application 模块的组织架构 Hook 系统
 * 为工作流等模块提供统一的组织架构数据获取能力
 */
class HookServiceProvider extends BaseHookServiceProvider
{
    /**
     * 可用的Hook定义类
     *
     * @var array<string>
     */
    protected array $hooks = [
        // 组织架构Hook定义
        OrganizationTypeHookDefinition::class,
        OrganizationMemberHookDefinition::class,
        DepartmentLeaderHookDefinition::class,
        SuperiorHookDefinition::class,
        // 用户相关Hook定义
        UserListHookDefinition::class,
        UserDetailHookDefinition::class,
        UserTypeHookDefinition::class,
    ];

    /**
     * Hook处理器映射数组
     *
     * @var array<string, array<string>>
     */
    protected array $hookHandlers = [
        //
        UserListHookDefinition::class => [
            //  用户列表Hook处理器 - 添加备用处理器用于测试单处理器功能
            // \Modules\Application\Hooks\Handlers\AnotherUserListHookHandler::class,
        ],
    ];

    /**
     * Hook订阅者数组
     *
     * @var array<string>
     */
    protected array $hookSubscribers = [
        // 组织架构Hook订阅者将由其他模块注册
        // Application模块主要负责定义Hook接口
    ];
}
