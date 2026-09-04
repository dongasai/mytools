<?php

namespace Modules\AFile\Api\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\AFile\Logics\FileTypeConfig;
use Modules\AFile\Models\FileImg;
use Modules\AFile\Services\FileService;
use Modules\AFile\Services\ImgService;

/**
 * 图片 API 控制器
 *
 * 提供图片上传、查看和下载的 RESTful API 接口
 */
class ImageApiController extends Controller
{
    /**
     * 获取当前用户 ID
     *
     * @param Request $request
     * @return int|null
     */
    private function getUserId(Request $request): ?int
    {
        $userId = $request->attributes->get('user_id');
        return $userId !== null ? (int) $userId : null;
    }

    /**
     * 获取图片列表（需要 ApiAuth 认证）
     *
     * 请求：GET /api/file/image
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
            'private' => 'nullable|integer|in:0,1',
            're_type' => 'nullable|string|max:50',
            're_id' => 'nullable|integer|min:0',
        ]);

        $userId = $this->getUserId($request);
        if ($userId === null) {
            return response()->json([
                'success' => false,
                'message' => '未认证',
                'code' => 401,
            ], 401);
        }

        $page = $validated['page'] ?? 1;
        $perPage = $validated['per_page'] ?? 20;

        // 通过 ImgService 获取图片列表
        $result = ImgService::getUserImageList($userId, $validated, $perPage, $page);

        return response()->json([
            'success' => true,
            'message' => '获取图片列表成功',
            'data' => $result,
            'code' => 200,
        ], 200);
    }

    /**
     * 上传公共图片（需要 ApiAuth 认证）
     *
     * 请求：POST /api/file/image/upload/public
     *
     * 注意：re_type和re_id仅用于上传时的类型验证，不保存到数据库。
     * 实际关联关系由业务API处理。
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadPublic(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file' => FileTypeConfig::imageRule(),
            're_type' => 'nullable|string|max:50',  // 仅用于验证，不保存
            're_id' => 'nullable|numeric|min:0',    // 仅用于验证，不保存
        ]);

        $userId = $this->getUserId($request);
        if ($userId === null) {
            return response()->json([
                'success' => false,
                'message' => '未认证',
                'code' => 401,
            ], 401);
        }

        // 上传图片，不建立业务关联
        $imageModel = FileService::uploadImage(
            $validated['file'],
            $userId,
            false,
            '',  // 不保存关联类型
            0    // 不保存关联ID
        );

        return $this->buildSuccessResponse($imageModel);
    }

    /**
     * 上传私有图片（需要 ApiAuth 认证）
     *
     * 请求：POST /api/file/image/upload/private
     *
     * 注意：re_type和re_id仅用于上传时的类型验证，不保存到数据库。
     * 实际关联关系由业务API处理。
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadPrivate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file' => FileTypeConfig::imageRule(),
            're_type' => 'nullable|string|max:50',  // 仅用于验证，不保存
            're_id' => 'nullable|numeric|min:0',    // 仅用于验证，不保存
        ]);

        $userId = $this->getUserId($request);
        if ($userId === null) {
            return response()->json([
                'success' => false,
                'message' => '未认证',
                'code' => 401,
            ], 401);
        }

        // 上传图片，不建立业务关联
        $imageModel = FileService::uploadImage(
            $validated['file'],
            $userId,
            true,
            '',  // 不保存关联类型
            0    // 不保存关联ID
        );

        return $this->buildSuccessResponse($imageModel, true);
    }

    /**
     * 查看图片（公开访问，直接输出图片）
     *
     * 请求：GET /api/file/image/{id}
     *
     * @param int $id 图片ID
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|JsonResponse
     */
    public function show(int $id)
    {
        $imageModel = ImgService::getImageById($id);

        if (!$imageModel) {
            return response()->json([
                'success' => false,
                'message' => '图片不存在',
                'code' => 404,
            ], 404);
        }

        if ($imageModel->private === 1) {
            return response()->json([
                'success' => false,
                'message' => '私有图片需要登录访问',
                'code' => 403,
            ], 403);
        }

        return ImgService::getResponse($imageModel);
    }

    /**
     * 下载图片（公开访问，仅下载公共图片）
     *
     * 请求：GET /api/file/image/{id}/download
     *
     * @param int $id 图片ID
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|JsonResponse
     */
    public function download(int $id)
    {
        $imageModel = ImgService::getImageById($id);

        if (!$imageModel) {
            return response()->json([
                'success' => false,
                'message' => '图片不存在',
                'code' => 404,
            ], 404);
        }

        if ($imageModel->private === 1) {
            return response()->json([
                'success' => false,
                'message' => '私有图片需要登录访问，请使用 /api/file/image/{id}/download/private',
                'code' => 403,
            ], 403);
        }

        return ImgService::download($imageModel);
    }

    /**
     * 下载私有图片（需要 ApiAuth 认证，检查所有权）
     *
     * 请求：GET /api/file/image/{id}/download/private
     *
     * @param Request $request
     * @param int $id 图片ID
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|JsonResponse
     */
    public function downloadPrivate(Request $request, int $id)
    {
        $imageModel = ImgService::getImageById($id);

        if (!$imageModel) {
            return response()->json([
                'success' => false,
                'message' => '图片不存在',
                'code' => 404,
            ], 404);
        }

        if ($imageModel->private !== 1) {
            return response()->json([
                'success' => false,
                'message' => '这不是私有图片，请使用公开下载接口',
                'code' => 400,
            ], 400);
        }

        $userId = $this->getUserId($request);
        if ($userId === null) {
            return response()->json([
                'success' => false,
                'message' => '未认证',
                'code' => 401,
            ], 401);
        }

        if ($imageModel->user_id !== $userId) {
            return response()->json([
                'success' => false,
                'message' => '无权限访问他人的私有图片',
                'code' => 403,
            ], 403);
        }

        return ImgService::download($imageModel);
    }

    /**
     * 构建统一的成功响应
     *
     * @param FileImg $imageModel
     * @param bool $isPrivate
     * @return JsonResponse
     */
    private function buildSuccessResponse(FileImg $imageModel, bool $isPrivate = false): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => '图片上传成功。图片ID需传给业务API建立关联。',
            'data' => [
                'id' => $imageModel->id,
                'original_name' => $imageModel->o_name,
                'file_size' => $imageModel->fsize,
                'width' => $imageModel->width,
                'height' => $imageModel->height,
                'file_type' => $imageModel->type1,
                'url' => ImgService::getPicUrl($imageModel),
                'is_private' => $imageModel->private,
                'created_at' => $imageModel->created_at->toIso8601String(),
            ],
            'code' => 200,
        ], 200);
    }
}
