<?php

declare(strict_types=1);

namespace Modules\Application\Hooks\Results;

use Modules\ABase\Hooks\Core\HookResult;
use Modules\Application\Hooks\Data\OrganizationType;

/**
 * 组织类型Hook结果类
 *
 * 用于返回系统中的组织架构类型列表
 */
class OrganizationTypeResult extends HookResult
{
    public function __construct(
        public readonly array $organization_types = [], // 组织类型数组：[OrganizationType实例]
        public readonly array $metadata = [],          // 元数据：['source_modules' => ['admin', 'shop']]
        bool $success = true,
        string $message = ''
    ) {}

    /**
     * 创建成功结果
     */
    public static function success(array $orgTypes, string $message = '获取组织类型成功'): static
    {
        return new static(
            organization_types: $orgTypes,
            metadata: [],
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
            message: '获取组织类型失败',
            metadata: $errors
        );
    }

    /**
     * 添加组织类型（链式累积）
     */
    public function addOrganizationType(OrganizationType $orgType): self
    {
        $newTypes = $this->organization_types;
        $newTypes[] = $orgType;

        return new static(
            organization_types: $newTypes,
            metadata: $this->metadata,
            success: $this->success,
            message: $this->message
        );
    }

    /**
     * 添加简化格式组织类型 - 向后兼容
     */
    public function addOrganizationTypeSimple(string $typeId, string $typeName, ?string $description = null): self
    {
        $orgType = OrganizationType::create($typeId, $typeName, $description);
        return $this->addOrganizationType($orgType);
    }

    /**
     * 合并组织类型数组
     */
    public function mergeOrganizationTypes(array $additionalOrgTypes): self
    {
        $mergedTypes = array_merge($this->organization_types, $additionalOrgTypes);

        return new static(
            organization_types: $mergedTypes,
            metadata: $this->metadata,
            success: $this->success,
            message: $this->message
        );
    }

    /**
     * 合并其他结果
     */
    public function merge(self $other): self
    {
        $mergedTypes = array_merge($this->organization_types, $other->organization_types);
        $mergedMetadata = array_merge_recursive($this->metadata, $other->metadata);

        return new static(
            organization_types: $mergedTypes,
            metadata: $mergedMetadata,
            success: $this->success && $other->success,
            message: $this->message ?: $other->message
        );
    }

    /**
     * 获取所有OrganizationType对象
     */
    public function getOrganizationTypes(): array
    {
        return $this->organization_types;
    }

    /**
     * 获取简化格式组织类型数组（向后兼容）
     */
    public function getOrganizationTypesSimple(): array
    {
        return array_map(fn($type) => $type->toArray(), $this->organization_types);
    }

    /**
     * 获取类型选项数组（用于表单）
     */
    public function getTypeOptions(): array
    {
        $options = [];
        foreach ($this->organization_types as $type) {
            $options[$type->type_id] = $type->type_name;
        }
        return $options;
    }

    /**
     * 获取详细选择器数组（用于下拉选择）
     */
    public function getSelectOptions(): array
    {
        return array_map(fn($type) => $type->toSelectOption(), $this->organization_types);
    }

    /**
     * 检查是否包含特定类型
     */
    public function hasType(string $typeId): bool
    {
        foreach ($this->organization_types as $type) {
            if ($type->type_id === $typeId) {
                return true;
            }
        }
        return false;
    }

    /**
     * 获取特定类型
     */
    public function getOrganizationType(string $typeId): ?OrganizationType
    {
        foreach ($this->organization_types as $type) {
            if ($type->type_id === $typeId) {
                return $type;
            }
        }
        return null;
    }

    /**
     * 按类型ID排序
     */
    public function sortByTypeId(): self
    {
        $sortedTypes = $this->organization_types;
        usort($sortedTypes, fn($a, $b) => strcmp($a->type_id, $b->type_id));

        return new static(
            organization_types: $sortedTypes,
            metadata: $this->metadata,
            success: $this->success,
            message: $this->message
        );
    }

    /**
     * 按类型名称排序
     */
    public function sortByName(): self
    {
        $sortedTypes = $this->organization_types;
        usort($sortedTypes, fn($a, $b) => strcmp($a->type_name, $b->type_name));

        return new static(
            organization_types: $sortedTypes,
            metadata: $this->metadata,
            success: $this->success,
            message: $this->message
        );
    }

    /**
     * 获取类型总数
     */
    public function getCount(): int
    {
        return count($this->organization_types);
    }

    /**
     * 检查结果是否为空
     */
    public function isEmpty(): bool
    {
        return empty($this->organization_types);
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
            'organization_types' => $this->getOrganizationTypesSimple(),
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
     * 获取第一个组织类型
     */
    public function getFirst(): ?OrganizationType
    {
        return $this->organization_types[0] ?? null;
    }

    /**
     * 获取最后一个组织类型
     */
    public function getLast(): ?OrganizationType
    {
        $types = $this->organization_types;
        return end($types) ?: null;
    }

    /**
     * 过滤出系统内置类型
     */
    public function getSystemTypes(): array
    {
        return array_filter($this->organization_types, fn($type) => $type->isSystemType());
    }

    /**
     * 过滤出自定义类型
     */
    public function getCustomTypes(): array
    {
        return array_filter($this->organization_types, fn($type) => !$type->isSystemType());
    }
}
