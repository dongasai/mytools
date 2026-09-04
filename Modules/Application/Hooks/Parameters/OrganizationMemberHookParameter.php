<?php

declare(strict_types=1);

namespace Modules\Application\Hooks\Parameters;

use Modules\ABase\Hooks\Core\HookParameter;

/**
 * 组织成员列表Hook参数类
 *
 * 用于根据组织类型获取成员列表
 */
class OrganizationMemberHookParameter extends HookParameter
{
    public function __construct(
        public readonly string $organization_type = '', // 组织类型：'department', 'role', 'position'
        public readonly int $organization_id = 0,       // 组织ID
        public readonly string $user_module_type = '',  // 用户模块类型：'admin', 'account', 'shop'
        public readonly string $search = '',            // 搜索关键词：'张三' 或 '技术部'
        public readonly int $limit = 50,                // 限制数量
        public readonly int $offset = 0                 // 分页
    ) {}
}
