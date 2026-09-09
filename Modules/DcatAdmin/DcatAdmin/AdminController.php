<?php

declare(strict_types=1);

namespace Modules\DcatAdmin\DcatAdmin;

use Dcat\Admin\Http\Controllers\AdminController as BaseAdminController;
use Dcat\Admin\Layout\Content;
use Illuminate\Http\JsonResponse;

/**
 * 后台控制器基类
 *
 * 继承 Dcat Admin 官方 AdminController，提供统一的响应方法
 *
 * @author AI 开发团队
 * @since 2026-09-06
 */
class AdminController extends BaseAdminController
{

    protected function vueview(Content $content,$viewname,$data){

        // standalone 模式直接返回视图（无 Dcat Admin 包裹）
        if (request()->get('standalone')) {
            return view($viewname,$data);
        }

        // 正常模式返回带 Dcat Admin 布局的响应
        return $content
            ->title('Vue 仪表盘')
            ->description('基于 Vue 3 的实时数据仪表盘')
            ->body(view($viewname,$data));
    }

    /**
     * 返回成功 JSON 响应
     *
     * @param mixed $data 响应数据
     * @param string $message 成功消息
     * @return JsonResponse
     */
    protected function success_json($data = null, string $message = '操作成功'): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'code' => 200
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response);
    }

    /**
     * 返回失败 JSON 响应
     *
     * @param string $message 错误消息
     * @param int $code HTTP 状态码
     * @return JsonResponse
     */
    protected function error_json(string $message = '操作失败', int $code = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'code' => $code
        ]);
    }
}
