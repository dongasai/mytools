<?php

declare(strict_types=1);

namespace Modules\Application\Hooks\Data;

use JsonSerializable;

/**
 * 组织类型数据类
 *
 * 表示系统中的组织架构类型，如部门、角色、职位等
 */
class OrganizationType implements JsonSerializable
{
    public function __construct(
        public readonly string $type_id,        // 组织类型标识：'department', 'role', 'position'
        public readonly string $type_name,      // 组织类型名称：'部门', '角色', '职位'
        public readonly ?string $description = null, // 组织类型描述
        public readonly ?string $icon = null,        // 图标标识
        public readonly ?array $metadata = null      // 扩展元数据
    ) {}

    /**
     * 创建组织类型实例
     */
    public static function create(string $typeId, string $typeName, ?string $description = null, ?string $icon = null, ?array $metadata = null): self
    {
        return new self(
            type_id: $typeId,
            type_name: $typeName,
            description: $description,
            icon: $icon,
            metadata: $metadata
        );
    }

    /**
     * 转换为数组
     */
    public function toArray(): array
    {
        return array_filter([
            'type_id' => $this->type_id,
            'type_name' => $this->type_name,
            'description' => $this->description,
            'icon' => $this->icon,
            'metadata' => $this->metadata
        ], fn($value) => $value !== null);
    }

    /**
     * 转换为简化格式
     */
    public function toSimpleFormat(): array
    {
        return [
            $this->type_id => $this->type_name
        ];
    }

    /**
     * 转换为选择器格式（用于表单下拉选项）
     */
    public function toSelectOption(): array
    {
        return [
            'value' => $this->type_id,
            'label' => $this->type_name,
            'description' => $this->description,
            'icon' => $this->icon
        ];
    }

    /**
     * 获取JSON序列化数据
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * 验证组织类型数据是否有效
     */
    public function isValid(): bool
    {
        return !empty($this->type_id) && !empty($this->type_name);
    }

    /**
     * 获取哈希值用于唯一标识
     */
    public function getHash(): string
    {
        return md5($this->type_id . $this->type_name);
    }

    /**
     * 检查是否为系统内置类型
     */
    public function isSystemType(): bool
    {
        $systemTypes = ['department', 'role', 'position', 'team', 'project'];
        return in_array($this->type_id, $systemTypes, true);
    }

    /**
     * 魔术方法：转换为字符串
     */
    public function __toString(): string
    {
        return $this->type_name;
    }

    /**
     * 比较两个组织类型是否相等
     */
    public function equals(self $other): bool
    {
        return $this->type_id === $other->type_id && $this->type_name === $other->type_name;
    }

    /**
     * 从数组创建组织类型实例
     */
    public static function fromArray(array $data): self
    {
        return new self(
            type_id: $data['type_id'] ?? '',
            type_name: $data['type_name'] ?? '',
            description: $data['description'] ?? null,
            icon: $data['icon'] ?? null,
            metadata: $data['metadata'] ?? null
        );
    }

    /**
     * 批量创建组织类型实例
     */
    public static function createMany(array $types): array
    {
        $instances = [];
        foreach ($types as $type) {
            if (is_array($type)) {
                $instances[] = self::fromArray($type);
            } elseif (is_string($type) && str_contains($type, ':')) {
                // 支持 "department:部门" 格式
                [$typeId, $typeName] = explode(':', $type, 2);
                $instances[] = self::create($typeId, $typeName);
            }
        }
        return $instances;
    }

    /**
     * 获取默认的组织类型列表
     */
    public static function getDefaultTypes(): array
    {
        return [
            self::create('department', '部门', '公司内部的部门组织'),
            self::create('role', '角色', '用户权限角色'),
            self::create('position', '职位', '员工职位信息'),
            self::create('team', '团队', '项目或业务团队'),
            self::create('project', '项目组', '临时项目组织')
        ];
    }
}