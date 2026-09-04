<?php

declare(strict_types=1);

namespace Modules\Application\Hooks\Handlers;

use Modules\ABase\Hooks\Core\HookHandlerInterface;
use Modules\ABase\Hooks\Core\HookParameterInterface;
use Modules\ABase\Hooks\Core\HookResultInterface;
use Modules\Application\Hooks\Results\UserListResult;
use Modules\Application\Dtos\User;

/**
 * 用户列表Hook处理器
 *
 * 演示如何处理用户列表Hook请求
 */
class UserListHookHandler implements HookHandlerInterface
{
    /**
     * 处理Hook请求
     */
    public static function handle(HookParameterInterface $parameter, HookResultInterface $result): HookResultInterface
    {
        $data = $parameter->toArray();

        // 演示数据：创建一些用户
        $users = [
            User::create(1, '张三', 'zhangsan@example.com', 'admin'),
            User::create(2, '李四', 'lisi@example.com', 'shop'),
            User::create(3, '王五', 'wangwu@example.com', 'account'),
            User::create(4, '赵六', 'zhaoliu@example.com', 'admin'),
            User::create(5, '钱七', 'qianqi@example.com', 'shop'),
        ];

        // 根据用户ID过滤
        if (!empty($data['ids'])) {
            $users = array_filter($users, fn($user) => in_array((string)$user->id, $data['ids']));
        }

        // 根据用户类型过滤
        if (!empty($data['type'])) {
            $users = array_filter($users, fn($user) => $user->type === $data['type']);
        }

        // 搜索过滤
        if (!empty($data['search'])) {
            $search = strtolower($data['search']);
            $users = array_filter(
                $users,
                fn($user) =>
                str_contains(strtolower($user->name), $search) ||
                    str_contains(strtolower($user->email), $search)
            );
        }

        // 分页处理
        $page = max(1, $data['page'] ?? 1);
        $limit = $data['limit'] ?? 50;
        $totalUsers = count($users);
        $offset = ($page - 1) * $limit;
        $pagedUsers = array_slice($users, $offset, $limit);

        // 创建分页信息
        $pagination = [
            'current_page' => $page,
            'per_page' => $limit,
            'total' => $totalUsers,
            'from' => $offset + 1,
            'to' => min($offset + $limit, $totalUsers),
            'has_more' => ($offset + $limit) < $totalUsers,
            'last_page' => (int) ceil($totalUsers / $limit),
        ];

        // 创建成功结果
        return new UserListResult(
            users: $pagedUsers,
            total_count: $totalUsers,
            current_page: $pagination['current_page'],
            per_page: $pagination['per_page'],
            last_page: $pagination['last_page'],
            has_more: $pagination['has_more'],
            metadata: [
                'source_module' => $data['type'] ?? 'default',
                'processed_at' => now()->toISOString(),
                'handler' => static::class,
                'filters_applied' => [
                    'ids' => !empty($data['ids']) ? count($data['ids']) : 0,
                    'type' => $data['type'] ?: null,
                    'search' => $data['search'] ?: null,
                ],
            ],
            success: true,
            message: '获取用户列表成功'
        );
    }

    /**
     * 判断是否应该执行
     */
    public static function shouldExecute(HookParameterInterface $parameter, HookResultInterface $result): bool
    {
        // 总是执行，只要有有效的参数就处理用户列表请求
        return true;
    }

    /**
     * 获取处理器优先级
     */
    public static function getPriority(): int
    {
        return 10;
    }
}
