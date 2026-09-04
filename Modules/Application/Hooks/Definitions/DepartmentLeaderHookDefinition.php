<?php

declare(strict_types=1);

namespace Modules\Application\Hooks\Definitions;

use Modules\ABase\Hooks\Core\HookDefinition;
use Modules\Application\Hooks\Parameters\DepartmentLeaderHookParameter;
use Modules\Application\Hooks\Results\DepartmentLeaderResult;

/**
 * 部门负责人Hook定义
 *
 * 用于获取部门的负责人信息，支持多种负责人类型和级别
 *
 * 用途：
 * - 自动指定部门负责人作为审批人
 * - 部门管理和权限控制
 * - 组织架构查询和展示
 *
 * 数据流向：
 * 传入部门ID → 各模块提供负责人信息 → 汇总返回
 *
 * 支持的负责人类型：
 * - manager: 部门经理
 * - director: 总监
 * - head: 主管
 * - leader: 领导者
 *
 * 返回数据包含：
 * - 负责人基本信息（ID、姓名、用户类型）
 * - 负责人类型和级别
 * - 部门信息
 * - 组织层级关系
 */
class DepartmentLeaderHookDefinition extends HookDefinition
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
    public string $description = '获取部门的负责人信息，支持多种负责人类型';

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->parameter_class = DepartmentLeaderHookParameter::class;
        $this->return_class = DepartmentLeaderResult::class;

        parent::__construct();
    }
}
