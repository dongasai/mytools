<?php

declare(strict_types=1);

namespace Modules\Application\Hooks\Results;

use Modules\ABase\Hooks\Core\HookResult;
use Modules\Application\Hooks\Data\OrganizationMember;

/**
 * 组织成员列表Hook结果类
 *
 * 用于返回根据组织类型筛选的成员列表
 */
class OrganizationMemberResult extends HookResult
{
    public function __construct(
        public readonly array $members = [],       // 成员列表：[OrganizationMember实例]
        public readonly int $total_count = 0,      // 总数量
        public readonly array $metadata = [],      // 元数据：['organization_info' => [...]]
        bool $success = true,
        string $message = ''
    ) {}

    /**
     * 创建成功结果
     */
    public static function success(array $members, int $totalCount = 0, array $metadata = []): static
    {
        return new static(
            members: $members,
            total_count: $totalCount ?: count($members),
            metadata: $metadata,
            success: true,
            message: '获取组织成员成功'
        );
    }

    /**
     * 创建失败结果
     */
    public static function failure(array $errors = []): static
    {
        return new static(
            success: false,
            message: '获取组织成员失败',
            metadata: $errors
        );
    }

    /**
     * 添加成员（链式累积）- 接受OrganizationMember对象
     */
    public function addMember(OrganizationMember $member): self
    {
        $newMembers = $this->members;
        $newMembers[] = $member;

        return new static(
            members: $newMembers,
            total_count: $this->total_count + 1,
            metadata: $this->metadata,
            success: $this->success,
            message: $this->message
        );
    }

    /**
     * 添加成员数组（链式累积）
     */
    public function addMembers(array $newMembers): self
    {
        $mergedMembers = array_merge($this->members, $newMembers);

        return new static(
            members: $mergedMembers,
            total_count: $this->total_count + count($newMembers),
            metadata: $this->metadata,
            success: $this->success,
            message: $this->message
        );
    }

    /**
     * 添加简化格式成员
     */
    public function addMemberSimple(int $id, string $orgType, string $userType, ?string $name = null): self
    {
        $member = OrganizationMember::create($id, $orgType, $userType, $name);
        return $this->addMember($member);
    }

    /**
     * 合并成员列表
     */
    public function mergeMemberLists(OrganizationMemberResult $other): self
    {
        $mergedMembers = array_merge($this->members, $other->members);
        $mergedMetadata = array_merge_recursive($this->metadata, $other->metadata);

        return new static(
            members: $mergedMembers,
            total_count: $this->total_count + $other->total_count,
            metadata: $mergedMetadata,
            success: $this->success && $other->success,
            message: $this->message ?: $other->message
        );
    }

    /**
     * 获取所有OrganizationMember对象
     */
    public function getMembers(): array
    {
        return $this->members;
    }

    /**
     * 获取简化格式成员数组
     */
    public function getMembersSimple(): array
    {
        return array_map(fn($member) => $member->toArray(), $this->members);
    }

    /**
     * 获取选择器格式成员数组
     */
    public function getSelectOptions(): array
    {
        return array_map(fn($member) => $member->toSelectOption(), $this->members);
    }

    /**
     * 根据组织类型过滤成员
     */
    public function getMembersByOrgType(string $orgType): array
    {
        return array_filter($this->members, fn($member) => $member->isOrganizationType($orgType));
    }

    /**
     * 根据用户类型过滤成员
     */
    public function getMembersByUserType(string $userType): array
    {
        return array_filter($this->members, fn($member) => $member->isUserType($userType));
    }

    /**
     * 检查是否包含特定成员
     */
    public function hasMember(int $memberId): bool
    {
        foreach ($this->members as $member) {
            if ($member->id === $memberId) {
                return true;
            }
        }
        return false;
    }

    /**
     * 获取特定成员
     */
    public function getMember(int $memberId): ?OrganizationMember
    {
        foreach ($this->members as $member) {
            if ($member->id === $memberId) {
                return $member;
            }
        }
        return null;
    }

    /**
     * 按用户ID排序
     */
    public function sortById(): self
    {
        $sortedMembers = $this->members;
        usort($sortedMembers, fn($a, $b) => $a->id <=> $b->id);

        return new static(
            members: $sortedMembers,
            total_count: $this->total_count,
            metadata: $this->metadata,
            success: $this->success,
            message: $this->message
        );
    }

    /**
     * 按姓名排序
     */
    public function sortByName(): self
    {
        $sortedMembers = $this->members;
        usort($sortedMembers, function ($a, $b) {
            $nameA = $a->name ?? '';
            $nameB = $b->name ?? '';
            return strcmp($nameA, $nameB);
        });

        return new static(
            members: $sortedMembers,
            total_count: $this->total_count,
            metadata: $this->metadata,
            success: $this->success,
            message: $this->message
        );
    }

    /**
     * 按组织类型分组
     */
    public function groupByOrganizationType(): array
    {
        $groups = [];
        foreach ($this->members as $member) {
            $groups[$member->organization_type][] = $member;
        }
        return $groups;
    }

    /**
     * 按用户类型分组
     */
    public function groupByUserType(): array
    {
        $groups = [];
        foreach ($this->members as $member) {
            $groups[$member->user_type][] = $member;
        }
        return $groups;
    }

    /**
     * 获取成员总数
     */
    public function getCount(): int
    {
        return count($this->members);
    }

    /**
     * 检查结果是否为空
     */
    public function isEmpty(): bool
    {
        return empty($this->members);
    }

    /**
     * 检查结果是否不为空
     */
    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }

    /**
     * 获取分页信息
     */
    public function getPaginationInfo(): array
    {
        return [
            'total' => $this->total_count,
            'count' => $this->getCount(),
            'has_more' => $this->getCount() < $this->total_count,
        ];
    }

    /**
     * 转换为数组
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'errors' => $this->errors,
            'message' => $this->message,
            'members' => $this->getMembersSimple(),
            'count' => $this->getCount(),
            'total_count' => $this->total_count,
            'pagination' => $this->getPaginationInfo(),
            'metadata' => $this->metadata,
        ];
    }

    /**
     * 实现 JsonSerializable 接口
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * 获取第一个成员
     */
    public function getFirst(): ?OrganizationMember
    {
        return $this->members[0] ?? null;
    }

    /**
     * 获取最后一个成员
     */
    public function getLast(): ?OrganizationMember
    {
        $members = $this->members;
        return end($members) ?: null;
    }

    /**
     * 获取管理员成员
     */
    public function getAdminMembers(): array
    {
        return $this->getMembersByUserType('admin');
    }

    /**
     * 获取普通用户成员
     */
    public function getAccountMembers(): array
    {
        return $this->getMembersByUserType('account');
    }

    /**
     * 获取租户用户成员
     */
    public function getShopMembers(): array
    {
        return $this->getMembersByUserType('shop');
    }

    /**
     * 限制返回数量
     */
    public function limit(int $limit): self
    {
        $limitedMembers = array_slice($this->members, 0, $limit);

        return new static(
            members: $limitedMembers,
            total_count: $this->total_count,
            metadata: $this->metadata,
            success: $this->success,
            message: $this->message
        );
    }

    /**
     * 偏移起始位置
     */
    public function offset(int $offset): self
    {
        $offsetMembers = array_slice($this->members, $offset);

        return new static(
            members: $offsetMembers,
            total_count: $this->total_count,
            metadata: $this->metadata,
            success: $this->success,
            message: $this->message
        );
    }
}
