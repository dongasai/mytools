<?php

declare(strict_types=1);

namespace Modules\Application\Hooks\Data;

use JsonSerializable;

/**
 * 组织架构数据类
 *
 * 表示系统中的具体组织架构节点，支持父子关系
 */
class Organization implements JsonSerializable
{
    public function __construct(
        public readonly string $id,                 // 组织ID（支持字符串格式，如"1-1"）
        public readonly string $name,               // 组织名称
        public readonly string $type,               // 组织类型：'department', 'role', 'position'
        public readonly ?string $parentId = null,   // 父级ID（null或"0"表示根级）
        public readonly ?string $code = null,       // 组织编码
        public readonly ?string $description = null, // 组织描述
        public readonly bool $is_active = true,     // 是否激活
        public readonly ?int $sort_order = null,    // 排序顺序
        public readonly ?string $path = null,       // 层级路径（如：/1/2/3）
        public readonly int $level = 1,             // 层级深度（1=根级）
        public readonly ?array $metadata = null     // 扩展元数据
    ) {}

    /**
     * 创建组织架构实例
     */
    public static function create(
        string $id,
        string $name,
        string $type,
        ?string $parentId = null,
        ?string $code = null,
        ?string $description = null,
        bool $is_active = true,
        ?int $sort_order = null,
        ?string $path = null,
        int $level = 1,
        ?array $metadata = null
    ): self {
        return new self(
            id: $id,
            name: $name,
            type: $type,
            parentId: $parentId,
            code: $code,
            description: $description,
            is_active: $is_active,
            sort_order: $sort_order,
            path: $path,
            level: $level,
            metadata: $metadata
        );
    }

    /**
     * 创建根级组织
     */
    public static function createRoot(
        string $id,
        string $name,
        string $type,
        ?string $code = null,
        ?string $description = null
    ): self {
        return new self(
            id: $id,
            name: $name,
            type: $type,
            parentId: '0',
            code: $code,
            description: $description,
            is_active: true,
            path: "/{$id}",
            level: 1
        );
    }

    /**
     * 创建子级组织
     */
    public static function createChild(
        string $id,
        string $name,
        string $type,
        string $parentId,
        ?string $parent_path = null,
        ?string $code = null,
        ?string $description = null
    ): self {
        $path = $parent_path ? "{$parent_path}/{$id}" : "/{$parentId}/{$id}";
        $level = substr_count($path, '/');

        return new self(
            id: $id,
            name: $name,
            type: $type,
            parentId: $parentId,
            code: $code,
            description: $description,
            is_active: true,
            path: $path,
            level: $level
        );
    }

    /**
     * 转换为数组
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'parentId' => $this->parentId,
            'code' => $this->code,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'path' => $this->path,
            'level' => $this->level,
            'metadata' => $this->metadata
        ];
    }

    /**
     * 转换为选择器格式（用于表单下拉选项）
     */
    public function toSelectOption(): array
    {
        $label = $this->name;
        if ($this->code) {
            $label = "[{$this->code}] {$this->name}";
        }

        return [
            'value' => $this->id,
            'label' => $label,
            'type' => $this->type,
            'parentId' => $this->parentId,
            'level' => $this->level,
            'is_active' => $this->is_active
        ];
    }

    /**
     * 转换为树形节点格式
     */
    public function toTreeNode(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'parentId' => $this->parentId,
            'level' => $this->level,
            'is_active' => $this->is_active,
            'children' => [] // 由调用方填充
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
     * 验证组织架构数据是否有效
     */
    public function isValid(): bool
    {
        return !empty($this->id) && !empty($this->name) && !empty($this->type);
    }

    /**
     * 检查是否为根级组织
     */
    public function isRoot(): bool
    {
        return $this->parentId === '0' || $this->parentId === null || $this->parentId === '';
    }

    /**
     * 检查是否为激活状态
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * 检查是否为指定类型的子级
     */
    public function isChildOf(string $parentId): bool
    {
        return $this->parentId === $parentId;
    }

    /**
     * 检查是否在指定路径下
     */
    public function isInPath(string $parentPath): bool
    {
        if (!$this->path) {
            return false;
        }

        return str_starts_with($this->path, $parentPath . '/');
    }

    /**
     * 获取显示名称（包含层级前缀）
     */
    public function getDisplayName(bool $showLevel = false): string
    {
        $prefix = '';
        if ($showLevel && $this->level > 1) {
            $prefix = str_repeat('　', $this->level - 1) . '├─ ';
        }

        $name = $this->name;
        if ($this->code) {
            $name = "[{$this->code}] {$name}";
        }

        return $prefix . $name;
    }

    /**
     * 获取层级缩进
     */
    public function getIndent(): string
    {
        return str_repeat('　　', $this->level - 1);
    }

    /**
     * 比较两个组织架构的层级
     */
    public function isDeeperThan(self $other): bool
    {
        return $this->level > $other->level;
    }

    /**
     * 比较两个组织架构是否为同一类型
     */
    public function isSameType(self $other): bool
    {
        return $this->type === $other->type;
    }

    /**
     * 从数组创建组织架构实例
     */
    public static function fromArray(array $data): self
    {
        // 支持两种字段名：parent_id 和 parentId
        $parentId = $data['parentId'] ?? $data['parent_id'] ?? null;

        return new self(
            id: (string)($data['id'] ?? ''),
            name: $data['name'] ?? '',
            type: $data['type'] ?? '',
            parentId: $parentId,
            code: $data['code'] ?? null,
            description: $data['description'] ?? null,
            is_active: (bool)($data['is_active'] ?? true),
            sort_order: isset($data['sort_order']) ? (int)$data['sort_order'] : null,
            path: $data['path'] ?? null,
            level: (int)($data['level'] ?? 1),
            metadata: $data['metadata'] ?? null
        );
    }

    /**
     * 批量创建组织架构实例
     */
    public static function createMany(array $organizations): array
    {
        $instances = [];
        foreach ($organizations as $org) {
            if (is_array($org)) {
                $instances[] = self::fromArray($org);
            }
        }
        return $instances;
    }

    /**
     * 魔术方法：转换为字符串
     */
    public function __toString(): string
    {
        return $this->getDisplayName();
    }
}