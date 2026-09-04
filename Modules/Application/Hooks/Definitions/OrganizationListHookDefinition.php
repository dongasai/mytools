<?php

declare(strict_types=1);

namespace Modules\Application\Hooks\Definitions;

use Modules\ABase\Hooks\Core\HookDefinition;
use Modules\Application\Hooks\Parameters\OrganizationListHookParameter;
use Modules\Application\Hooks\Results\OrganizationListResult;

/**
 * 组织架构列表Hook定义
 *
 * 用于获取执行类型的组织架构列表，支持平铺显示和层级关系
 *
 * 用途：
 * - 工作流配置时选择具体的组织架构节点
 * - 组织架构树形结构的平铺展示
 * - 支持父子关系的组织架构数据获取
 *
 * 特点：
 * - 支持平铺显示（所有节点在同一层级）
 * - 支持pid字段表示父子关系
 * - 支持按组织类型过滤
 * - 支持用户权限过滤
 *
 * 数据流向：
 * 各模块提供组织架构数据 → Application模块汇总 → 消费模块使用
 */
class OrganizationListHookDefinition extends HookDefinition
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
    public string $description = '获取执行类型的组织架构列表（平铺显示，支持父子关系）';

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->parameter_class = OrganizationListHookParameter::class;
        $this->return_class = OrganizationListResult::class;

        parent::__construct();
    }
}
