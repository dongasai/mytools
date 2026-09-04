<?php

declare(strict_types=1);

namespace Modules\Application\Hooks\Parameters;

use Modules\Workflow\Enums\USER_TYPE;
use Modules\ABase\Hooks\Core\HookParameter;

/**
 * 上级关系Hook参数类
 *
 * 用于获取用户的上级领导
 */
class SuperiorHookParameter extends HookParameter
{
    public function __construct(
        public readonly USER_TYPE $user_type,            // 人员类型：cc/approver/notifier
        public readonly int $user_id = 0,                // 用户ID
        public readonly string $relationship_type = '',  // 关系类型：'direct', 'functional', 'reporting'
        public readonly int $max_levels = 1,             // 最大层级
        public readonly bool $include_active_only = true  // 仅包含活跃上级
    ) {}
}
