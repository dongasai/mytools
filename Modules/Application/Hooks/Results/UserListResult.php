<?php

namespace Modules\Application\Hooks\Results;

use Illuminate\Support\Facades\Log;
use Modules\ABase\Hooks\Core\HookResult;
use Modules\Application\Dtos\User;

/**
 * 用户列表Hook结果类
 */
class UserListResult extends HookResult
{
    public function __construct(
        public readonly array $users = [],           // 用户列表：[User实例]
        public readonly int $total_count = 0,        // 总记录数
        public readonly int $current_page = 1,       // 当前页码
        public readonly int $per_page = 50,          // 每页数量
        public readonly int $last_page = 1,          // 最后一页
        public readonly bool $has_more = false,      // 是否有下一页
        public readonly array $metadata = [],        // 元数据：['source_modules' => ['admin'], 'filters_applied' => [...]]
        bool $success = true,
        string $message = '',
        bool $processed = false,
        string $processor = ''
    ) {
        parent::__construct($success, $message, $processed, $processor);
    }

    public static function success(array $users, string $message = '获取用户列表成功', ?string $processor = null): static
    {
        return new static(
            users: $users,
            total_count: count($users),
            current_page: 1,
            per_page: count($users),
            last_page: 1,
            has_more: false,
            metadata: [],
            success: true,
            message: $message,
            processed: true,
            processor: $processor ?? static::class
        );
    }

    public static function failure(array $errors = [], string $processor = 'unknown'): static
    {
        return new static(
            success: false,
            message: '获取用户列表失败',
            metadata: $errors,
            processed: false,
            processor: $processor
        );
    }

    /**
     * 创建未处理的结果（处理器无法处理该请求）
     */
    public static function unprocessed(string $reason = '处理器无法处理该请求', string $processor = 'unknown'): static
    {
        Log::debug("hook unprocessed " . $reason);
        return new static(
            users: [],
            total_count: 0,
            current_page: 1,
            per_page: 50,
            last_page: 1,
            has_more: false,
            metadata: [],
            success: true,
            message: $reason,
            processed: false,
            processor: $processor
        );
    }

    // 添加用户（链式累积）- 接受User对象
    public function addUser(User $user): self
    {
        $newUsers = $this->users;
        $newUsers[] = $user;

        return new static(
            users: $newUsers,
            total_count: $this->total_count + 1,
            current_page: $this->current_page,
            per_page: $this->per_page,
            last_page: $this->last_page,
            has_more: $this->has_more,
            metadata: $this->metadata,
            success: $this->success,
            message: $this->message
        );
    }

    // 添加用户数组（链式累积）
    public function addUsers(array $newUsers): self
    {
        $mergedUsers = array_merge($this->users, $newUsers);

        return new static(
            users: $mergedUsers,
            total_count: $this->total_count + count($newUsers),
            current_page: $this->current_page,
            per_page: $this->per_page,
            last_page: $this->last_page,
            has_more: $this->has_more,
            metadata: $this->metadata,
            success: $this->success,
            message: $this->message
        );
    }

    // 添加简化格式用户 - 向后兼容
    public function addUserSimple(int $id, string $name, string $type, array $attributes = []): self
    {
        $user = User::create(
            $id,
            $name,
            $type,
            $attributes['email'] ?? '',
            $attributes['avatar'] ?? ''
        );
        return $this->addUser($user);
    }

    // 合并用户列表
    public function mergeUserLists(UserListResult $other): self
    {
        $mergedUsers = array_merge($this->users, $other->users);
        $mergedMetadata = array_merge_recursive($this->metadata, $other->metadata);

        return new static(
            users: $mergedUsers,
            total_count: $this->total_count + $other->total_count,
            current_page: $this->current_page,
            per_page: $this->per_page,
            last_page: max($this->last_page, $other->last_page),
            has_more: $this->has_more || $other->has_more,
            metadata: $mergedMetadata,
            success: $this->success && $other->success,
            message: $this->message ?: $other->message
        );
    }

    // 获取所有User对象
    public function getUsers(): array
    {
        return $this->users;
    }

    // 获取简化格式用户数组（向后兼容）
    public function getUsersSimple(): array
    {
        return array_map(fn($user) => $user->toArray(), $this->users);
    }

    // 根据类型过滤用户
    public function getUsersByType(string $type): array
    {
        return array_filter($this->users, fn($user) => $user->type === $type);
    }

    // 搜索用户
    public function searchUsers(string $keyword): array
    {
        return array_filter($this->users, function ($user) use ($keyword) {
            return str_contains($user->name, $keyword) ||
                str_contains($user->email, $keyword) ||
                str_contains($user->type, $keyword);
        });
    }

    // 检查是否包含特定用户
    public function hasUser(int $userId): bool
    {
        foreach ($this->users as $user) {
            if ($user->id === $userId) {
                return true;
            }
        }
        return false;
    }

    // 获取特定用户
    public function getUser(int $userId): ?User
    {
        foreach ($this->users as $user) {
            if ($user->id === $userId) {
                return $user;
            }
        }
        return null;
    }

    // 获取用户总数
    public function getCount(): int
    {
        return $this->total_count;
    }

    // 设置分页信息
    public function withPagination(int $totalCount, int $currentPage, int $perPage, int $lastPage, bool $hasMore): self
    {
        return new static(
            users: $this->users,
            total_count: $totalCount,
            current_page: $currentPage,
            per_page: $perPage,
            last_page: $lastPage,
            has_more: $hasMore,
            metadata: $this->metadata,
            success: $this->success,
            message: $this->message,
            processed: $this->isProcessed(),
            processor: $this->getProcessor()
        );
    }

    // 设置元数据
    public function withMetadata(array $metadata): self
    {
        return new static(
            users: $this->users,
            total_count: $this->total_count,
            current_page: $this->current_page,
            per_page: $this->per_page,
            last_page: $this->last_page,
            has_more: $this->has_more,
            metadata: $metadata,
            success: $this->success,
            message: $this->message,
            processed: $this->isProcessed(),
            processor: $this->getProcessor()
        );
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
            'users' => $this->getUsersSimple(),
            'pagination' => [
                'total_count' => $this->total_count,
                'current_page' => $this->current_page,
                'per_page' => $this->per_page,
                'last_page' => $this->last_page,
                'has_more' => $this->has_more,
                'from' => (($this->current_page - 1) * $this->per_page) + 1,
                'to' => min($this->current_page * $this->per_page, $this->total_count),
            ],
            'metadata' => $this->metadata,
        ];
    }

    /**
     * 检查结果是否为空
     */
    public function isEmpty(): bool
    {
        return empty($this->users);
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
