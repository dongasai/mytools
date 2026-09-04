<?php

declare(strict_types=1);

namespace Modules\Application\Hooks\Results;

use Modules\ABase\Hooks\Core\HookResult;
use Modules\Application\Hooks\Data\Organization;

/**
 * 组织架构列表Hook结果类
 *
 * 用于返回系统中的组织架构列表（平铺显示）
 */
class OrganizationListResult extends HookResult
{
    public function __construct(
        public readonly array $organizations = [],      // 组织架构数组：[Organization实例]
        public readonly array $metadata = [],          // 元数据：['source_modules' => ['admin', 'hospital'], 'total_count' => 50]
        bool $success = true,
        string $message = ''
    ) {}

    /**
     * 创建成功结果
     */
    public static function success(array $organizations, string $message = '获取组织架构列表成功'): static
    {
        return new static(
            organizations: $organizations,
            metadata: ['total_count' => count($organizations)],
            success: true,
            message: $message
        );
    }

    /**
     * 创建失败结果
     */
    public static function failure(array $errors = []): static
    {
        return new static(
            success: false,
            message: '获取组织架构列表失败',
            metadata: $errors
        );
    }

    /**
     * 添加组织架构（链式累积）
     */
    public function addOrganization(Organization $organization): self
    {
        $newOrganizations = $this->organizations;
        $newOrganizations[] = $organization;

        return new static(
            organizations: $newOrganizations,
            metadata: array_merge($this->metadata, ['total_count' => count($newOrganizations)]),
            success: $this->success,
            message: $this->message
        );
    }

    /**
     * 添加简化格式组织架构
     */
    public function addOrganizationSimple(
        string $id,
        string $name,
        string $type,
        ?string $parentId = null,
        ?string $code = null,
        ?string $description = null
    ): self {
        $organization = Organization::create(
            id: $id,
            name: $name,
            type: $type,
            parentId: $parentId,
            code: $code,
            description: $description
        );
        return $this->addOrganization($organization);
    }

    /**
     * 合并组织架构数组
     */
    public function mergeOrganizations(array $additionalOrganizations): self
    {
        $mergedOrganizations = array_merge($this->organizations, $additionalOrganizations);

        return new static(
            organizations: $mergedOrganizations,
            metadata: array_merge($this->metadata, ['total_count' => count($mergedOrganizations)]),
            success: $this->success,
            message: $this->message
        );
    }

    /**
     * 合并其他结果
     */
    public function merge(self $other): self
    {
        $mergedOrganizations = array_merge($this->organizations, $other->organizations);
        $mergedMetadata = array_merge_recursive($this->metadata, $other->metadata);

        return new static(
            organizations: $mergedOrganizations,
            metadata: array_merge($mergedMetadata, ['total_count' => count($mergedOrganizations)]),
            success: $this->success && $other->success,
            message: $this->message ?: $other->message
        );
    }

    /**
     * 获取所有Organization对象
     */
    public function getOrganizations(): array
    {
        return $this->organizations;
    }

    /**
     * 获取简化格式组织架构数组
     */
    public function getOrganizationsSimple(): array
    {
        return array_map(fn($org) => $org->toArray(), $this->organizations);
    }

    /**
     * 获取选择器数组（用于表单下拉选项）
     */
    public function getSelectOptions(): array
    {
        return array_map(fn($org) => $org->toSelectOption(), $this->organizations);
    }

    /**
     * 按ID索引的组织架构数组
     */
    public function getOrganizationsById(): array
    {
        $indexed = [];
        foreach ($this->organizations as $org) {
            $indexed[$org->id] = $org;
        }
        return $indexed;
    }

    /**
     * 按父级ID分组的组织架构数组
     */
    public function getOrganizationsByParent(): array
    {
        $grouped = [];
        foreach ($this->organizations as $org) {
            $parentId = $org->parentId ?? '0';
            $grouped[$parentId][] = $org;
        }
        return $grouped;
    }

    /**
     * 按类型分组的组织架构数组
     */
    public function getOrganizationsByType(): array
    {
        $grouped = [];
        foreach ($this->organizations as $org) {
            $grouped[$org->type][] = $org;
        }
        return $grouped;
    }

    /**
     * 获取根级组织架构
     */
    public function getRootOrganizations(): array
    {
        return array_filter($this->organizations, fn($org) => $org->isRoot());
    }

    /**
     * 获取指定父级的子级组织架构
     */
    public function getChildOrganizations(?string $parentId = null): array
    {
        if ($parentId === null) {
            return $this->getRootOrganizations();
        }

        return array_filter($this->organizations, fn($org) => $org->parentId === $parentId);
    }

    /**
     * 获取指定类型的组织架构
     */
    public function getOrganizationsByTypeFilter(string $type): array
    {
        return array_filter($this->organizations, fn($org) => $org->type === $type);
    }

    /**
     * 获取激活的组织架构
     */
    public function getActiveOrganizations(): array
    {
        return array_filter($this->organizations, fn($org) => $org->isActive());
    }

    /**
     * 搜索组织架构
     */
    public function searchOrganizations(string $keyword): array
    {
        $keyword = strtolower(trim($keyword));
        if (empty($keyword)) {
            return $this->organizations;
        }

        return array_filter($this->organizations, function ($org) use ($keyword) {
            return str_contains(strtolower($org->name), $keyword) ||
                str_contains(strtolower($org->code ?? ''), $keyword) ||
                str_contains(strtolower($org->description ?? ''), $keyword);
        });
    }

    /**
     * 检查是否包含特定ID的组织架构
     */
    public function hasOrganization(string $id): bool
    {
        foreach ($this->organizations as $org) {
            if ($org->id === $id) {
                return true;
            }
        }
        return false;
    }

    /**
     * 获取特定ID的组织架构
     */
    public function getOrganization(string $id): ?Organization
    {
        foreach ($this->organizations as $org) {
            if ($org->id === $id) {
                return $org;
            }
        }
        return null;
    }

    /**
     * 按ID排序
     */
    public function sortById(): self
    {
        $sortedOrganizations = $this->organizations;
        usort($sortedOrganizations, fn($a, $b) => strcmp($a->id, $b->id));

        return new static(
            organizations: $sortedOrganizations,
            metadata: $this->metadata,
            success: $this->success,
            message: $this->message
        );
    }

    /**
     * 按名称排序
     */
    public function sortByName(): self
    {
        $sortedOrganizations = $this->organizations;
        usort($sortedOrganizations, fn($a, $b) => strcmp($a->name, $b->name));

        return new static(
            organizations: $sortedOrganizations,
            metadata: $this->metadata,
            success: $this->success,
            message: $this->message
        );
    }

    /**
     * 按层级排序
     */
    public function sortByLevel(): self
    {
        $sortedOrganizations = $this->organizations;
        usort($sortedOrganizations, fn($a, $b) => $a->level <=> $b->level ?: $a->sort_order <=> $b->sort_order);

        return new static(
            organizations: $sortedOrganizations,
            metadata: $this->metadata,
            success: $this->success,
            message: $this->message
        );
    }

    /**
     * 按排序顺序排序
     */
    public function sortByOrder(): self
    {
        $sortedOrganizations = $this->organizations;
        usort($sortedOrganizations, fn($a, $b) => ($a->sort_order ?? 0) <=> ($b->sort_order ?? 0));

        return new static(
            organizations: $sortedOrganizations,
            metadata: $this->metadata,
            success: $this->success,
            message: $this->message
        );
    }

    /**
     * 获取组织架构总数
     */
    public function getCount(): int
    {
        return count($this->organizations);
    }

    /**
     * 检查结果是否为空
     */
    public function isEmpty(): bool
    {
        return empty($this->organizations);
    }

    /**
     * 检查结果是否不为空
     */
    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
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
            'organizations' => $this->getOrganizationsSimple(),
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
     * 获取第一个组织架构
     */
    public function getFirst(): ?Organization
    {
        return $this->organizations[0] ?? null;
    }

    /**
     * 获取最后一个组织架构
     */
    public function getLast(): ?Organization
    {
        $organizations = $this->organizations;
        return end($organizations) ?: null;
    }

    /**
     * 过滤出指定类型的组织架构
     */
    public function filterByType(string $type): self
    {
        $filtered = array_filter($this->organizations, fn($org) => $org->type === $type);

        return new static(
            organizations: $filtered,
            metadata: array_merge($this->metadata, ['filtered_count' => count($filtered)]),
            success: $this->success,
            message: $this->message
        );
    }

    /**
     * 过滤出激活的组织架构
     */
    public function filterActive(): self
    {
        $filtered = array_filter($this->organizations, fn($org) => $org->isActive());

        return new static(
            organizations: $filtered,
            metadata: array_merge($this->metadata, ['filtered_count' => count($filtered)]),
            success: $this->success,
            message: $this->message
        );
    }
}
