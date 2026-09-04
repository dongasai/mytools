<?php

declare(strict_types=1);

namespace Modules\Application\Hooks\Handlers;

use Modules\ABase\Hooks\Core\HookHandlerInterface;
use Modules\ABase\Hooks\Core\HookParameterInterface;
use Modules\ABase\Hooks\Core\HookResultInterface;
use Modules\Application\Hooks\Results\OrganizationTypeResult;
use Modules\Application\Hooks\Data\OrganizationType;

/**
 * 组织类型Hook处理器
 *
 * 演示如何处理组织类型Hook请求
 */
class OrganizationTypeHookHandler implements HookHandlerInterface
{
    /**
     * 处理Hook请求
     */
    public static function handle(HookParameterInterface $parameter, HookResultInterface $result): HookResultInterface
    {
        $data = $parameter->toArray();

        // 演示数据：创建一些组织类型
        $organizationTypes = [
            OrganizationType::create('department', '部门', '公司内部部门组织'),
            OrganizationType::create('role', '角色', '用户角色权限组织'),
            OrganizationType::create('position', '职位', '员工职位层级'),
            OrganizationType::create('team', '团队', '项目团队组织'),
            OrganizationType::create('committee', '委员会', '专项委员会'),
        ];

        // 如果指定了组织类型过滤
        if (!empty($data['org_type'])) {
            $organizationTypes = array_filter(
                $organizationTypes,
                fn($type) => $type->type_id === $data['org_type']
            );
        }

        // 创建成功结果
        return OrganizationTypeResult::success($organizationTypes, '获取组织类型成功');
    }

    /**
     * 判断是否应该执行
     */
    public static function shouldExecute(HookParameterInterface $parameter, HookResultInterface $result): bool
    {
        $data = $parameter->toArray();
        return !empty($data['source']);
    }

    /**
     * 获取处理器优先级
     */
    public static function getPriority(): int
    {
        return 10;
    }
}
