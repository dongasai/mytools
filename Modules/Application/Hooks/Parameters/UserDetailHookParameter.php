<?php

declare(strict_types=1);

namespace Modules\Application\Hooks\Parameters;

use Modules\ABase\Hooks\Core\HookParameter;

/**
 * 用户详情Hook参数类
 */
class UserDetailHookParameter extends HookParameter
{
    public function __construct(
        public readonly int $user_id = 0,                    // 用户ID
        public readonly bool $include_roles = false,        // 是否包含角色信息
        public readonly bool $include_permissions = false    // 是否包含权限信息
    ) {}

    /**
     * 创建参数实例
     */
    public static function create(
        int $user_id = 0,
        bool $include_roles = false,
        bool $include_permissions = false
    ): self {
        return new self(
            user_id: $user_id,
            include_roles: $include_roles,
            include_permissions: $include_permissions
        );
    }

    /**
     * 转换为数组
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->user_id,
            'include_roles' => $this->include_roles,
            'include_permissions' => $this->include_permissions,
        ];
    }

    /**
     * 验证参数
     */
    protected function validate(): void
    {
        if ($this->user_id <= 0) {
            throw new \InvalidArgumentException('用户ID必须大于0');
        }
    }
}
