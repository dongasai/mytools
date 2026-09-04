<?php

declare(strict_types=1);

namespace Modules\Application\Hooks\Handlers;

use Modules\ABase\Hooks\Core\HookHandlerInterface;
use Modules\ABase\Hooks\Core\HookParameterInterface;
use Modules\ABase\Hooks\Core\HookResultInterface;
use Modules\Application\Hooks\Results\UserListResult;
use Modules\Application\Dtos\User;

/**
 * 通用用户列表Hook处理器（用于测试单处理器功能）
 *
 * 优先级较低，用于验证单处理器Hook在找到有效结果后会停止执行
 * 处理非工作流相关的普通用户类型，对工作流专用用户类型返回未处理状态
 */
class AnotherUserListHookHandler implements HookHandlerInterface
{
    /**
     * 处理Hook请求
     */
    public static function handle(HookParameterInterface $parameter, HookResultInterface $result): HookResultInterface
    {
        $data = $parameter->toArray();

        // 如果调试模式，记录此处理器被执行了
        if (function_exists('logger')) {
            logger()->info('[AnotherUserListHookHandler] 通用处理器被执行');
        }

        // 创建一些测试用户
        $users = [
            User::create(
                id: 999,
                name: '备用用户',
                type: 'backup',
                email: 'backup@example.com',
                avatar: '/avatars/backup.jpg'
            ),
            User::create(
                id: 888,
                name: '临时用户',
                type: 'temp',
                email: 'temp@example.com',
                avatar: '/avatars/temp.jpg'
            ),
        ];

        // 应用过滤条件
        if (!empty($data['type'])) {
            $users = array_filter($users, fn($user) => $user->type === $data['type']);
        }

        // 对于工作流专用用户类型，返回未处理状态，让其他专门的处理器处理
        if ($data['type'] === 'approver' || $data['type'] === 'department_manager' || $data['type'] === 'superior') {
            return UserListResult::unprocessed(
                reason: '通用处理器不支持工作流专用用户类型，需要专门的处理器提供数据',
                processor: static::class
            );
        }

        // 只处理非工作流类型的用户
        if (!empty($data['type'])) {
            $users = array_filter($users, fn($user) => $user->type === $data['type']);
        }

        return UserListResult::success(
            users: array_values($users),
            message: '获取用户列表成功（来自备用处理器）',
            processor: static::class
        )->withPagination(
            totalCount: count($users),
            currentPage: $data['page'] ?? 1,
            perPage: $data['limit'] ?? 50,
            lastPage: 1,
            hasMore: false
        )->withMetadata([
            'source_module' => 'application',
            'handler' => static::class,
            'processed_at' => now()->toISOString(),
            'note' => '这是通用处理器，处理非工作流相关的用户类型'
        ]);
    }

    /**
     * 判断是否应该执行
     */
    public static function shouldExecute(HookParameterInterface $parameter, HookResultInterface $result): bool
    {
        return true; // 总是执行
    }

    /**
     * 获取处理器优先级
     *
     * @return int 优先级数值（1-100，数值越小越早执行）
     */
    public static function getPriority(): int
    {
        return 10; // 较高优先级，先尝试通用处理器
    }
}
