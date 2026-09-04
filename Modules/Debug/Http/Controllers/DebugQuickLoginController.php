<?php

declare(strict_types=1);

namespace Modules\Debug\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Debug\Hooks\Merchant2Hook;

/**
 * 快速登录API控制器
 *
 * 开发环境调试用，通过uid直接获取token
 */
class DebugQuickLoginController extends Controller
{
    /**
     * 快速登录
     *
     * 通过uid直接登录，返回token和用户数据
     */
    public function login(Request $request): JsonResponse
    {
        $uid = $request->input('uid');

        if (!$uid) {
            return response()->json([
                'success' => false,
                'message' => 'uid参数必填',
            ], 400);
        }

        $result = Merchant2Hook::quickLogin((int) $uid);

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        return response()->json($result);
    }
}
