<?php

declare(strict_types=1);

namespace Modules\Application\Hooks\Definitions;

use Modules\ABase\Hooks\Core\HookDefinition;
use Modules\Application\Hooks\Parameters\OrganizationMemberHookParameter;
use Modules\Application\Hooks\Results\OrganizationMemberResult;

/**
 * 组织成员列表Hook定义
 *
 * 用于根据组织类型获取成员列表，支持多模块数据聚合和搜索过滤
 *
 * 用途：
 * - 选择特定组织类型下的成员作为审批人或通知对象
 * - 工作流中的人员选择组件
 * - 组织架构下的成员管理
 *
 * 数据流向：
 * 传入组织类型和过滤条件 → 各模块提供对应成员 → 汇总返回
 *
 * 支持的过滤条件：
 * - organization_type: 组织类型（部门、角色、职位等）
 * - user_module_type: 用户模块类型（admin、account、shop）
 * - search: 搜索关键词（姓名、邮箱等）
 * - limit/offset: 分页参数
 */
class OrganizationMemberHookDefinition extends HookDefinition
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
    public string $description = '根据组织类型获取成员列表，支持搜索和分页';

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->parameter_class = OrganizationMemberHookParameter::class;
        $this->return_class = OrganizationMemberResult::class;

        parent::__construct();
    }
}
