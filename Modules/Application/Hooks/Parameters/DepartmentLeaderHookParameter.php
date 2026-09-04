<?php

declare(strict_types=1);

namespace Modules\Application\Hooks\Parameters;

use Modules\Workflow\Enums\USER_TYPE;
use Modules\ABase\Hooks\Core\HookParameter;

/**
 * 部门负责人Hook参数类
 *
 * 用于获取部门的负责人信息
 */
class DepartmentLeaderHookParameter extends HookParameter
{
    public function __construct(
        public readonly USER_TYPE $user_type,           // 人员类型：cc/approver/notifier
        public readonly int $department_id = 0,        // 部门ID
        public readonly array $leader_types = [],      // 负责人类型：['manager', 'director', 'head']
        public readonly bool $include_secondary = false // 包含副负责人
    ) {}
}
