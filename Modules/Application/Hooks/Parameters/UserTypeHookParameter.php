<?php

declare(strict_types=1);

namespace Modules\Application\Hooks\Parameters;

use Modules\ABase\Hooks\Core\HookParameter;

/**
 * 用户类型Hook参数类
 */
class UserTypeHookParameter extends HookParameter
{
    public function __construct(
        public readonly string $source = 'admin',         // 源模块
        public readonly bool $include_system = true,       // 是否包含系统类型
        public readonly bool $active_only = false          // 是否只获取活跃类型
    ) {}

    /**
     * 创建参数实例
     */
    public static function create(
        string $source = 'admin',
        bool $include_system = true,
        bool $active_only = false
    ): self {
        return new self(
            source: $source,
            include_system: $include_system,
            active_only: $active_only
        );
    }

    /**
     * 转换为数组
     */
    public function toArray(): array
    {
        return [
            'source' => $this->source,
            'include_system' => $this->include_system,
            'active_only' => $this->active_only,
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
    }
}
