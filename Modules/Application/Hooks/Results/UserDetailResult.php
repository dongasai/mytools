<?php

namespace Modules\Application\Hooks\Results;

use Modules\ABase\Hooks\Core\HookResult;
use Modules\Application\Dtos\User;

/**
 * 用户详情Hook结果类
 */
class UserDetailResult extends HookResult
{
    public function __construct(
        public readonly ?User $user = null,            // 用户详情：User对象
        public readonly array $metadata = [],          // 元数据：['source_module' => 'admin', 'permissions' => [...]]
        bool $success = true,
        string $message = ''
    ) {}

    public static function success(User $user, array $metadata = []): static
    {
        return new static(
            user: $user,
            metadata: $metadata,
            success: true,
            message: '获取用户详情成功'
        );
    }

    public static function failure(array $errors = []): static
    {
        return new static(
            success: false,
            message: '获取用户详情失败',
            metadata: $errors
        );
    }

    // 设置用户信息
    public function withUser(User $user): self
    {
        return new static(
            user: $user,
            metadata: $this->metadata,
            success: $this->success,
            message: $this->message
        );
    }

    // 获取用户信息
    public function getUser(): ?User
    {
        return $this->user;
    }

    // 获取用户信息数组
    public function getUserArray(): ?array
    {
        return $this->user?->toArray();
    }

    // 检查是否有用户信息
    public function hasUser(): bool
    {
        return $this->user !== null;
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
            'user' => $this->getUserArray(),
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
}
