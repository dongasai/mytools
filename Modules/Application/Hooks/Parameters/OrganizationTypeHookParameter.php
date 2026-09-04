<?php

declare(strict_types=1);

namespace Modules\Application\Hooks\Parameters;

use Modules\ABase\Hooks\Core\HookParameter;

/**
 * 组织类型Hook参数类
 *
 * 用于获取系统中可用的组织架构类型，如部门、角色、职位等
 */
class OrganizationTypeHookParameter extends HookParameter
{
    public function __construct(
        public readonly string $source = 'admin',        // 源模块
        public readonly ?int $user_id = null,            // 用户ID
        public readonly ?string $org_type = null,        // 组织类型过滤
        public readonly bool $include_system = true      // 是否包含系统类型
    ) {}

    /**
     * 创建参数实例
     */
    public static function create(
        string $source = 'admin',
        ?int $user_id = null,
        ?string $org_type = null,
        bool $include_system = true
    ): self {
        return new self(
            source: $source,
            user_id: $user_id,
            org_type: $org_type,
            include_system: $include_system
        );
    }

    /**
     * 转换为数组
     */
    public function toArray(): array
    {
        return [
            'source' => $this->source,
            'user_id' => $this->user_id,
            'org_type' => $this->org_type,
            'include_system' => $this->include_system,
        ];
    }

    /**
     * 验证参数
     */
    protected function validate(): void
    {
        if (empty($this->source)) {
            throw new \InvalidArgumentException('源模块不能为空');
        }
    }
}
