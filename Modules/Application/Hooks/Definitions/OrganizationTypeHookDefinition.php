<?php

declare(strict_types=1);

namespace Modules\Application\Hooks\Definitions;

use Modules\ABase\Hooks\Core\HookDefinition;
use Modules\Application\Hooks\Parameters\OrganizationTypeHookParameter;
use Modules\Application\Hooks\Results\OrganizationTypeResult;

/**
 * 组织类型Hook定义
 *
 * 用于获取系统中可用的组织架构类型，支持多模块数据聚合
 *
 * 用途：
 * - 工作流配置时选择组织类型（如部门、角色、职位）
 * - 为其他Hook提供组织类型筛选依据
 * - 统一管理各模块提供的组织类型数据
 *
 * 数据流向：
 * 各模块提供组织类型 → Application模块汇总 → 消费模块使用
 */
class OrganizationTypeHookDefinition extends HookDefinition
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
    public string $description = '获取系统中可用的组织架构类型';

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->parameter_class = OrganizationTypeHookParameter::class;
        $this->return_class = OrganizationTypeResult::class;

        parent::__construct();
    }
}
