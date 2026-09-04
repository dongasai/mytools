<?php

declare(strict_types=1);

namespace Modules\Debug\Hooks;

use Modules\ABase\Hooks\Management\Hooks;
use Modules\ABase\Hooks\Parameters\MapHookParameter;
use Modules\Debug\Hooks\Definitions\QuickLoginHook;

/**
 * Merchant2 Hook助手类
 *
 * 提供简化的Hook调用接口，用于从Debug模块调用Merchant2快速登录功能
 */
class Merchant2Hook
{
    /**
     * 快速登录：通过uid直接获取token和用户数据
     *
     * @param int $uid 租户用户ID（enterprise_users.id）
     * @return array{success: bool, token: string|null, user: array|null, message: string}
     */
    public static function quickLogin(int $uid): array
    {
        if (!Hooks::hasHandlers(QuickLoginHook::class)) {
            return [
                'success' => false,
                'token' => null,
                'user' => null,
                'message' => '快速登录Hook未注册，请检查Merchant2模块的DebugHookServiceProvider',
            ];
        }

        $parameter = new MapHookParameter(['uid' => $uid]);
        $result = Hooks::apply(QuickLoginHook::class, $parameter);

        if (!$result->isSuccess()) {
            return [
                'success' => false,
                'token' => null,
                'user' => null,
                'message' => $result->getMessage() ?: '快速登录失败',
            ];
        }

        $data = $result->getData();

        return [
            'success' => true,
            'token' => $data['token'] ?? null,
            'user' => $data['user'] ?? null,
            'message' => $result->getMessage() ?: '快速登录成功',
        ];
    }
}
