<?php

declare(strict_types=1);

namespace Modules\Application\Hooks\Definitions;

use Modules\ABase\Hooks\Core\HookDefinition;
use Modules\Application\Hooks\Parameters\SuperiorHookParameter;
use Modules\Application\Hooks\Results\SuperiorResult;

/**
 * 上级关系Hook定义
 *
 * 用于获取用户的上级领导，支持多种关系类型和层级控制
 *
 * 用途：
 * - 自动将审批流转给上级
 * - 组织层级关系查询
 * - 权限继承和上报路径
 *
 * 数据流向：
 * 传入用户ID和关系类型 → 各模块提供上级关系 → 汇总返回
 *
 * 支持的关系类型：
 * - direct: 直接汇报关系
 * - functional: 职能管理关系
 * - reporting: 组织汇报关系
 *
 * 控制参数：
 * - max_levels: 最大查询层级
 * - include_active_only: 仅包含活跃上级
 *
 * 返回数据包含：
 * - 上级基本信息
 * - 关系类型和层级
 * - 组织路径信息
 * - 权限范围描述
 */
class SuperiorHookDefinition extends HookDefinition
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
    public string $description = '获取用户的上级领导，支持多种关系类型和层级控制';

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->parameter_class = SuperiorHookParameter::class;
        $this->return_class = SuperiorResult::class;

        parent::__construct();
    }
}
