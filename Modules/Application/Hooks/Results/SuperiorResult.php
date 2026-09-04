<?php

declare(strict_types=1);

namespace Modules\Application\Hooks\Results;

use Modules\ABase\Hooks\Core\HookResult;

/**
 * 上级关系Hook结果类
 *
 * 用于返回用户的上级领导信息
 */
class SuperiorResult extends HookResult
{
    public function __construct(
        public readonly array $superiors = [],      // 上级列表：[{id: 3, name: '上级领导', relationship: 'direct', level: 1, user_type: 'admin'}]
        public readonly array $metadata = [],       // 元数据：['org_hierarchy' => [...], 'relationship_chain' => [...]]
        bool $success = true,
        string $message = ''
    ) {}

    /**
     * 创建成功结果
     */
    public static function success(array $superiors, array $metadata = []): static
    {
        return new static(
            superiors: $superiors,
            metadata: $metadata,
            success: true,
            message: '获取上级关系成功'
        );
    }

    /**
     * 创建失败结果
     */
    public static function failure(array $errors = []): static
    {
        return new static(
            success: false,
            message: '获取上级关系失败',
            metadata: $errors
        );
    }

    /**
     * 添加上级
     */
    public function addSuperior(array $superior): self
    {
        $newSuperiors = $this->superiors;
        $newSuperiors[] = $superior;

        return new static(
            superiors: $newSuperiors,
            metadata: $this->metadata,
            success: $this->success,
            message: $this->message
        );
    }

    /**
     * 添加多个上级
     */
    public function addSuperiors(array $superiors): self
    {
        $mergedSuperiors = array_merge($this->superiors, $superiors);

        return new static(
            superiors: $mergedSuperiors,
            metadata: $this->metadata,
            success: $this->success,
            message: $this->message
        );
    }

    /**
     * 按层级获取上级
     */
    public function getSuperiorsByLevel(int $level): array
    {
        return array_filter($this->superiors, fn($superior) => ($superior['level'] ?? 0) === $level);
    }

    /**
     * 获取直接上级
     */
    public function getDirectSuperiors(): array
    {
        return $this->getSuperiorsByLevel(1);
    }

    /**
     * 获取二级上级
     */
    public function getLevel2Superiors(): array
    {
        return $this->getSuperiorsByLevel(2);
    }

    /**
     * 按关系类型获取上级
     */
    public function getSuperiorsByRelationship(string $relationship): array
    {
        return array_filter($this->superiors, fn($superior) => ($superior['relationship'] ?? '') === $relationship);
    }

    /**
     * 获取直接汇报关系上级
     */
    public function getReportingSuperiors(): array
    {
        return $this->getSuperiorsByRelationship('reporting');
    }

    /**
     * 获取职能关系上级
     */
    public function getFunctionalSuperiors(): array
    {
        return $this->getSuperiorsByRelationship('functional');
    }

    /**
     * 按用户类型获取上级
     */
    public function getSuperiorsByUserType(string $userType): array
    {
        return array_filter($this->superiors, fn($superior) => ($superior['user_type'] ?? '') === $userType);
    }

    /**
     * 获取管理员上级
     */
    public function getAdminSuperiors(): array
    {
        return $this->getSuperiorsByUserType('admin');
    }

    /**
     * 获取上级总数
     */
    public function getCount(): int
    {
        return count($this->superiors);
    }

    /**
     * 检查是否有上级
     */
    public function hasSuperiors(): bool
    {
        return !empty($this->superiors);
    }

    /**
     * 检查是否有特定层级的上级
     */
    public function hasSuperiorAtLevel(int $level): bool
    {
        return !empty($this->getSuperiorsByLevel($level));
    }

    /**
     * 检查是否有特定关系类型的上级
     */
    public function hasSuperiorRelationship(string $relationship): bool
    {
        return !empty($this->getSuperiorsByRelationship($relationship));
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
            'superiors' => $this->superiors,
            'count' => $this->getCount(),
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
     * 获取上级姓名列表
     */
    public function getSuperiorNames(): array
    {
        return array_map(fn($superior) => $superior['name'] ?? '', $this->superiors);
    }

    /**
     * 获取上级ID列表
     */
    public function getSuperiorIds(): array
    {
        return array_map(fn($superior) => $superior['id'] ?? 0, $this->superiors);
    }

    /**
     * 按层级分组上级
     */
    public function groupByLevel(): array
    {
        $groups = [];
        foreach ($this->superiors as $superior) {
            $level = $superior['level'] ?? 0;
            $groups[$level][] = $superior;
        }
        ksort($groups);
        return $groups;
    }

    /**
     * 按关系类型分组上级
     */
    public function groupByRelationship(): array
    {
        $groups = [];
        foreach ($this->superiors as $superior) {
            $relationship = $superior['relationship'] ?? 'unknown';
            $groups[$relationship][] = $superior;
        }
        return $groups;
    }

    /**
     * 按用户类型分组上级
     */
    public function groupByUserType(): array
    {
        $groups = [];
        foreach ($this->superiors as $superior) {
            $userType = $superior['user_type'] ?? 'unknown';
            $groups[$userType][] = $superior;
        }
        return $groups;
    }

    /**
     * 检查是否包含特定用户
     */
    public function hasSuperior(int $userId): bool
    {
        foreach ($this->superiors as $superior) {
            if (($superior['id'] ?? 0) === $userId) {
                return true;
            }
        }
        return false;
    }

    /**
     * 获取特定上级信息
     */
    public function getSuperior(int $userId): ?array
    {
        foreach ($this->superiors as $superior) {
            if (($superior['id'] ?? 0) === $userId) {
                return $superior;
            }
        }
        return null;
    }

    /**
     * 获取最高层级的上级
     */
    public function getTopLevelSuperior(): ?array
    {
        if (empty($this->superiors)) {
            return null;
        }

        $topLevel = 0;
        $topSuperior = null;

        foreach ($this->superiors as $superior) {
            $level = $superior['level'] ?? 0;
            if ($level > $topLevel) {
                $topLevel = $level;
                $topSuperior = $superior;
            }
        }

        return $topSuperior;
    }

    /**
     * 获取所有关系类型
     */
    public function getRelationshipTypes(): array
    {
        $types = array_unique(array_map(fn($superior) => $superior['relationship'] ?? '', $this->superiors));
        return array_filter($types);
    }

    /**
     * 获取所有用户类型
     */
    public function getUserTypes(): array
    {
        $types = array_unique(array_map(fn($superior) => $superior['user_type'] ?? '', $this->superiors));
        return array_filter($types);
    }

    /**
     * 按层级排序
     */
    public function sortByLevel(): self
    {
        $sortedSuperiors = $this->superiors;
        usort($sortedSuperiors, fn($a, $b) => ($a['level'] ?? 0) <=> ($b['level'] ?? 0));

        return new static(
            superiors: $sortedSuperiors,
            metadata: $this->metadata,
            success: $this->success,
            message: $this->message
        );
    }

    /**
     * 获取上级关系链
     */
    public function getRelationshipChain(): array
    {
        $chain = $this->groupByLevel();
        $result = [];

        foreach ($chain as $level => $superiors) {
            $result[$level] = array_map(fn($superior) => [
                'id' => $superior['id'] ?? 0,
                'name' => $superior['name'] ?? '',
                'relationship' => $superior['relationship'] ?? '',
                'user_type' => $superior['user_type'] ?? ''
            ], $superiors);
        }

        return $result;
    }

    /**
     * 检查结果是否为空
     */
    public function isEmpty(): bool
    {
        return empty($this->superiors);
    }

    /**
     * 检查结果是否不为空
     */
    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }

    /**
     * 限制返回层级数量
     */
    public function limitLevels(int $maxLevel): self
    {
        $limitedSuperiors = array_filter($this->superiors, fn($superior) => ($superior['level'] ?? 0) <= $maxLevel);

        return new static(
            superiors: $limitedSuperiors,
            metadata: $this->metadata,
            success: $this->success,
            message: $this->message
        );
    }

    /**
     * 获取特定层级的第一个上级
     */
    public function getFirstSuperiorAtLevel(int $level): ?array
    {
        $superiors = $this->getSuperiorsByLevel($level);
        return empty($superiors) ? null : reset($superiors);
    }

    /**
     * 获取第一个直接上级
     */
    public function getFirstDirectSuperior(): ?array
    {
        return $this->getFirstSuperiorAtLevel(1);
    }
}
