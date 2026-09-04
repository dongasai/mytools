<?php

declare(strict_types=1);

namespace Modules\Application\Hooks\Parameters;

use Modules\ABase\Hooks\Core\HookParameter;

/**
 * 组织架构列表Hook参数类
 *
 * 用于获取组织架构列表的参数配置，支持平铺显示和层级关系
 */
class OrganizationListHookParameter extends HookParameter
{
    public function __construct(
        public readonly string $source = 'admin',           // 源模块
        public readonly ?int $user_id = null,               // 用户ID（权限过滤）
        public readonly ?string $org_type = null,           // 组织类型过滤：'department', 'role', 'position'
        public readonly ?int $parent_id = null,             // 父级ID（获取特定层级）
        public readonly bool $include_children = true,      // 是否包含子级节点
        public readonly bool $active_only = true,           // 仅显示激活的组织
        public readonly array $exclude_ids = [],            // 排除的组织ID列表
        public readonly ?string $search_keyword = null,     // 搜索关键词
        public readonly int $limit = 0,                     // 限制返回数量（0=不限制）
        public readonly bool $include_metadata = true       // 是否包含元数据
    ) {}

    /**
     * 创建参数实例
     */
    public static function create(
        string $source = 'admin',
        ?int $user_id = null,
        ?string $org_type = null,
        ?int $parent_id = null,
        bool $include_children = true,
        bool $active_only = true,
        array $exclude_ids = [],
        ?string $search_keyword = null,
        int $limit = 0,
        bool $include_metadata = true
    ): self {
        return new self(
            source: $source,
            user_id: $user_id,
            org_type: $org_type,
            parent_id: $parent_id,
            include_children: $include_children,
            active_only: $active_only,
            exclude_ids: $exclude_ids,
            search_keyword: $search_keyword,
            limit: $limit,
            include_metadata: $include_metadata
        );
    }

    /**
     * 创建获取根级节点的参数
     */
    public static function forRootNodes(string $source = 'admin', ?string $org_type = null): self
    {
        return new self(
            source: $source,
            org_type: $org_type,
            parent_id: 0, // 获取根级节点
            include_children: true
        );
    }

    /**
     * 创建按用户权限过滤的参数
     */
    public static function forUser(?int $user_id, string $source = 'admin', ?string $org_type = null): self
    {
        return new self(
            source: $source,
            user_id: $user_id,
            org_type: $org_type,
            active_only: true
        );
    }

    /**
     * 创建搜索参数
     */
    public static function forSearch(string $keyword, string $source = 'admin', ?string $org_type = null): self
    {
        return new self(
            source: $source,
            org_type: $org_type,
            search_keyword: $keyword,
            active_only: true
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
            'parent_id' => $this->parent_id,
            'include_children' => $this->include_children,
            'active_only' => $this->active_only,
            'exclude_ids' => $this->exclude_ids,
            'search_keyword' => $this->search_keyword,
            'limit' => $this->limit,
            'include_metadata' => $this->include_metadata,
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

        if ($this->limit < 0) {
            throw new \InvalidArgumentException('限制数量不能为负数');
        }

        if ($this->parent_id !== null && $this->parent_id < 0) {
            throw new \InvalidArgumentException('父级ID不能为负数');
        }
    }

    /**
     * 检查是否需要过滤用户权限
     */
    public function requiresUserPermission(): bool
    {
        return $this->user_id !== null;
    }

    /**
     * 检查是否需要搜索
     */
    public function requiresSearch(): bool
    {
        return !empty($this->search_keyword);
    }

    /**
     * 检查是否需要按类型过滤
     */
    public function requiresTypeFilter(): bool
    {
        return !empty($this->org_type);
    }

    /**
     * 获取参数摘要（用于日志记录）
     */
    public function getSummary(): string
    {
        $parts = [];
        $parts[] = "source:{$this->source}";

        if ($this->org_type) {
            $parts[] = "type:{$this->org_type}";
        }

        if ($this->user_id) {
            $parts[] = "user:{$this->user_id}";
        }

        if ($this->parent_id !== null) {
            $parts[] = "parent:{$this->parent_id}";
        }

        $parts[] = $this->active_only ? 'active' : 'all';

        return implode(', ', $parts);
    }
}
