<?php

namespace Modules\Application\Hooks\Parameters;

use Modules\ABase\Hooks\Core\HookParameter;
use Modules\Application\Dtos\User;

/**
 * 用户列表Hook参数类
 */
class UserListHookParameter extends HookParameter
{
    public function __construct(
        public readonly string $type = '',           // 用户类型：'account', 'admin', 'shop'
        public readonly string $search = '',         // 搜索关键词：'张三' 或 'admin'
        public readonly array $ids = [],             // 用户ID数组：['1', '2', '3']
        public readonly int $page = 1,               // 页码：从1开始
        public readonly int $limit = 50              // 每页数量
    ) {}

    /**
     * 创建参数实例
     */
    public static function create(
        ?string $type = null,
        ?string $search = null,
        ?array $ids = null,
        int $page = 1,
        int $limit = 50
    ): self {
        return new self(
            type: $type ?? '',
            search: $search ?? '',
            ids: $ids ?? [],
            page: $page,
            limit: $limit
        );
    }

    /**
     * 转换为数组
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'search' => $this->search,
            'ids' => $this->ids,
            'page' => $this->page,
            'limit' => $this->limit,
        ];
    }

    /**
     * 验证参数
     */
    protected function validate(): void
    {
        if ($this->limit < 1 || $this->limit > 1000) {
            throw new \InvalidArgumentException('每页数量必须在1-1000之间');
        }
        if ($this->page < 1) {
            throw new \InvalidArgumentException('页码必须从1开始');
        }
    }
}
