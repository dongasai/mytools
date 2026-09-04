<?php

declare(strict_types=1);

namespace Modules\Application\Hooks\Results;

use Modules\ABase\Hooks\Core\HookResult;

/**
 * 部门负责人Hook结果类
 *
 * 用于返回部门的负责人信息
 */
class DepartmentLeaderResult extends HookResult
{
    public function __construct(
        public readonly array $leaders = [],       // 负责人列表：[{id: 1, name: '部门经理', type: 'manager', level: 1, user_type: 'admin'}]
        public readonly array $department = [],    // 部门信息：['id' => 1, 'name' => '技术部']
        public readonly array $metadata = [],      // 元数据：['organization_hierarchy' => [...]]
        bool $success = true,
        string $message = ''
    ) {}

    /**
     * 创建成功结果
     */
    public static function success(array $leaders, array $department = [], array $metadata = []): static
    {
        return new static(
            leaders: $leaders,
            department: $department,
            metadata: $metadata,
            success: true,
            message: '获取部门负责人成功'
        );
    }

    /**
     * 创建失败结果
     */
    public static function failure(array $errors = []): static
    {
        return new static(
            success: false,
            message: '获取部门负责人失败',
            metadata: $errors
        );
    }

    /**
     * 添加负责人
     */
    public function addLeader(array $leader): self
    {
        $newLeaders = $this->leaders;
        $newLeaders[] = $leader;

        return new static(
            leaders: $newLeaders,
            department: $this->department,
            metadata: $this->metadata,
            success: $this->success,
            message: $this->message
        );
    }

    /**
     * 添加多个负责人
     */
    public function addLeaders(array $leaders): self
    {
        $mergedLeaders = array_merge($this->leaders, $leaders);

        return new static(
            leaders: $mergedLeaders,
            department: $this->department,
            metadata: $this->metadata,
            success: $this->success,
            message: $this->message
        );
    }

    /**
     * 设置部门信息
     */
    public function setDepartment(array $department): self
    {
        return new static(
            leaders: $this->leaders,
            department: $department,
            metadata: $this->metadata,
            success: $this->success,
            message: $this->message
        );
    }

    /**
     * 获取主要负责人（级别最高的）
     */
    public function getPrimaryLeader(): ?array
    {
        if (empty($this->leaders)) {
            return null;
        }

        // 按级别排序，返回级别最高的
        $sortedLeaders = $this->leaders;
        usort($sortedLeaders, fn($a, $b) => ($a['level'] ?? 999) <=> ($b['level'] ?? 999));

        return $sortedLeaders[0] ?? null;
    }

    /**
     * 按级别获取负责人
     */
    public function getLeadersByLevel(int $level): array
    {
        return array_filter($this->leaders, fn($leader) => ($leader['level'] ?? 0) === $level);
    }

    /**
     * 按类型获取负责人
     */
    public function getLeadersByType(string $type): array
    {
        return array_filter($this->leaders, fn($leader) => ($leader['type'] ?? '') === $type);
    }

    /**
     * 获取经理类型负责人
     */
    public function getManagers(): array
    {
        return $this->getLeadersByType('manager');
    }

    /**
     * 获取总监类型负责人
     */
    public function getDirectors(): array
    {
        return $this->getLeadersByType('director');
    }

    /**
     * 获取主管类型负责人
     */
    public function getHeads(): array
    {
        return $this->getLeadersByType('head');
    }

    /**
     * 获取负责人总数
     */
    public function getCount(): int
    {
        return count($this->leaders);
    }

    /**
     * 检查是否有负责人
     */
    public function hasLeaders(): bool
    {
        return !empty($this->leaders);
    }

    /**
     * 检查是否有特定类型的负责人
     */
    public function hasLeaderType(string $type): bool
    {
        return !empty($this->getLeadersByType($type));
    }

    /**
     * 获取部门名称
     */
    public function getDepartmentName(): string
    {
        return $this->department['name'] ?? '';
    }

    /**
     * 获取部门ID
     */
    public function getDepartmentId(): int
    {
        return $this->department['id'] ?? 0;
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
            'leaders' => $this->leaders,
            'count' => $this->getCount(),
            'primary_leader' => $this->getPrimaryLeader(),
            'department' => $this->department,
            'metadata' => $this->metadata,
        ];
    }

    /**
     * 获取负责人姓名列表
     */
    public function getLeaderNames(): array
    {
        return array_map(fn($leader) => $leader['name'] ?? '', $this->leaders);
    }

    /**
     * 获取负责人ID列表
     */
    public function getLeaderIds(): array
    {
        return array_map(fn($leader) => $leader['id'] ?? 0, $this->leaders);
    }

    /**
     * 按级别分组负责人
     */
    public function groupByLevel(): array
    {
        $groups = [];
        foreach ($this->leaders as $leader) {
            $level = $leader['level'] ?? 0;
            $groups[$level][] = $leader;
        }
        ksort($groups);
        return $groups;
    }

    /**
     * 按类型分组负责人
     */
    public function groupByType(): array
    {
        $groups = [];
        foreach ($this->leaders as $leader) {
            $type = $leader['type'] ?? 'unknown';
            $groups[$type][] = $leader;
        }
        return $groups;
    }

    /**
     * 检查是否包含特定用户
     */
    public function hasLeader(int $userId): bool
    {
        foreach ($this->leaders as $leader) {
            if (($leader['id'] ?? 0) === $userId) {
                return true;
            }
        }
        return false;
    }

    /**
     * 获取特定用户信息
     */
    public function getLeader(int $userId): ?array
    {
        foreach ($this->leaders as $leader) {
            if (($leader['id'] ?? 0) === $userId) {
                return $leader;
            }
        }
        return null;
    }

    /**
     * 获取所有用户类型
     */
    public function getUserTypes(): array
    {
        $types = array_unique(array_map(fn($leader) => $leader['user_type'] ?? '', $this->leaders));
        return array_filter($types);
    }

    /**
     * 检查结果是否为空
     */
    public function isEmpty(): bool
    {
        return empty($this->leaders);
    }

    /**
     * 检查结果是否不为空
     */
    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }

    /**
     * 实现 JsonSerializable 接口
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
