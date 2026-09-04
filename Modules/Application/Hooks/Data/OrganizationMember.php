<?php

declare(strict_types=1);

namespace Modules\Application\Hooks\Data;

use JsonSerializable;

/**
 * 组织成员数据类
 *
 * 表示组织中的成员信息，包含用户ID、组织类型和用户类型
 */
class OrganizationMember implements JsonSerializable
{
    public function __construct(
        public readonly int $id,                    // 用户ID
        public readonly string $organization_type,   // 组织类型：'department', 'role', 'position'
        public readonly string $user_type,          // 用户类型：'account', 'admin', 'shop'
        public readonly ?string $name = null,       // 用户姓名
        public readonly ?string $email = null,      // 邮箱地址
        public readonly ?string $phone = null,      // 电话号码
        public readonly ?array $organization_info = null, // 组织信息：['id' => 1, 'name' => '技术部']
        public readonly ?array $metadata = null     // 扩展元数据
    ) {}

    /**
     * 创建成员实例
     */
    public static function create(int $id, string $organizationType, string $userType, ?string $name = null, ?array $metadata = null): self
    {
        return new self(
            id: $id,
            organization_type: $organizationType,
            user_type: $userType,
            name: $name,
            metadata: $metadata
        );
    }

    /**
     * 转换为数组
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'organization_type' => $this->organization_type,
            'user_type' => $this->user_type,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'organization_info' => $this->organization_info,
            'metadata' => $this->metadata
        ], fn($value) => $value !== null);
    }

    /**
     * 转换为简化格式
     */
    public function toSimpleFormat(): array
    {
        return [
            'id' => $this->id,
            'organization_type' => $this->organization_type,
            'user_type' => $this->user_type
        ];
    }

    /**
     * 转换为选择器格式（用于表单下拉选项）
     */
    public function toSelectOption(): array
    {
        return [
            'value' => $this->id,
            'label' => $this->name ?? "用户#{$this->id}",
            'description' => "{$this->organization_type} - {$this->user_type}",
            'organization_type' => $this->organization_type,
            'user_type' => $this->user_type
        ];
    }

    /**
     * 获取显示名称
     */
    public function getDisplayName(): string
    {
        return $this->name ?? "用户#{$this->id}";
    }

    /**
     * 获取用户标识
     */
    public function getIdentifier(): string
    {
        return "{$this->user_type}_{$this->id}";
    }

    /**
     * 获取JSON序列化数据
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * 验证成员数据是否有效
     */
    public function isValid(): bool
    {
        return $this->id > 0 && !empty($this->organization_type) && !empty($this->user_type);
    }

    /**
     * 检查是否为管理员类型
     */
    public function isAdmin(): bool
    {
        return $this->user_type === 'admin';
    }

    /**
     * 检查是否为普通用户类型
     */
    public function isAccount(): bool
    {
        return $this->user_type === 'account';
    }

    /**
     * 检查是否为租户用户类型
     */
    public function isShop(): bool
    {
        return $this->user_type === 'shop';
    }

    /**
     * 检查组织类型是否匹配
     */
    public function isOrganizationType(string $type): bool
    {
        return $this->organization_type === $type;
    }

    /**
     * 检查用户类型是否匹配
     */
    public function isUserType(string $type): bool
    {
        return $this->user_type === $type;
    }

    /**
     * 获取组织信息
     */
    public function getOrganizationInfo(): array
    {
        return $this->organization_info ?? [];
    }

    /**
     * 获取组织名称
     */
    public function getOrganizationName(): ?string
    {
        return $this->organization_info['name'] ?? null;
    }

    /**
     * 魔术方法：转换为字符串
     */
    public function __toString(): string
    {
        return $this->getDisplayName();
    }

    /**
     * 比较两个成员是否相等
     */
    public function equals(self $other): bool
    {
        return $this->id === $other->id
            && $this->organization_type === $other->organization_type
            && $this->user_type === $other->user_type;
    }

    /**
     * 从数组创建成员实例
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? 0,
            organization_type: $data['organization_type'] ?? '',
            user_type: $data['user_type'] ?? '',
            name: $data['name'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            organization_info: $data['organization_info'] ?? null,
            metadata: $data['metadata'] ?? null
        );
    }

    /**
     * 批量创建成员实例
     */
    public static function createMany(array $members): array
    {
        $instances = [];
        foreach ($members as $member) {
            if (is_array($member)) {
                // 支持简化格式：[1, 'department', 'admin']
                if (count($member) === 3 && is_int($member[0]) && is_string($member[1]) && is_string($member[2])) {
                    $instances[] = self::create($member[0], $member[1], $member[2]);
                } else {
                    // 支持完整数组格式
                    $instances[] = self::fromArray($member);
                }
            }
        }
        return $instances;
    }

    /**
     * 按组织类型筛选成员
     */
    public static function filterByOrganizationType(array $members, string $organizationType): array
    {
        return array_filter($members, fn(self $member) => $member->isOrganizationType($organizationType));
    }

    /**
     * 按用户类型筛选成员
     */
    public static function filterByUserType(array $members, string $userType): array
    {
        return array_filter($members, fn(self $member) => $member->isUserType($userType));
    }
}