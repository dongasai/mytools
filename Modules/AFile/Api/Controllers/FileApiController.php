<?php

namespace Modules\AFile\Api\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\AFile\Logics\FileTypeConfig;
use Modules\AFile\Services\FileService;
use Modules\AFile\Services\ModuleFileService;

/**
 * 文件 API 控制器
 *
 * 提供文件上传和下载的 RESTful API 接口
 */
class FileApiController extends Controller
{
    /**
     * 获取文件列表（需要 ApiAuth 认证）
     *
     * 请求：GET /api/file
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
            're_type' => 'nullable|string|max:50',
            're_id' => 'nullable|integer|min:0',
            'type1' => 'nullable|string|max:50',
        ]);

        // 获取认证用户ID
        $userId = $request->attributes->get('user_id');
        if ($userId === null) {
            return response()->json([
                'success' => false,
                'message' => '未认证',
                'code' => 401,
            ], 401);
        }

        $page = $validated['page'] ?? 1;
        $perPage = $validated['per_page'] ?? 20;

        // 通过 FileService 静态方法获取用户文件列表
        $result = FileService::getUserFileList($userId, $validated);

        return response()->json([
            'success' => true,
            'message' => '获取文件列表成功',
            'data' => $result,
            'code' => 200,
        ], 200);
    }

    /**
     * 上传公开文件（需要 ApiAuth 认证）
     *
     * 请求：POST /api/file/upload/public
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
            'file' => FileTypeConfig::fileRule(),
            're_type' => 'nullable|string|max:50',  // 仅用于验证，不保存
            're_id' => 'nullable|numeric|min:0',    // 仅用于验证，不保存
        ]);

        $userId = $request->attributes->get('user_id');
        if ($userId === null) {
            return response()->json([
                'success' => false,
                'message' => '未认证',
                'code' => 401,
            ], 401);
        }

        // 上传文件，不建立业务关联
        $fileModel = FileService::uploadFile(
            $validated['file'],
            (int) $userId,
            '',  // 不保存关联类型
            0    // 不保存关联ID
        );

        return response()->json([
            'success' => true,
            'message' => '文件上传成功。文件ID需传给业务API建立使用标记。',
            'data' => [
                'id' => $fileModel->id,
                'original_name' => $fileModel->o_name,
                'file_size' => $fileModel->fsize,
                'file_type' => $fileModel->type1,
                'url' => FileService::getFileUrl($fileModel->id),
                'status' => $fileModel->status,
                'created_at' => $fileModel->created_at->toIso8601String(),
            ],
            'code' => 200,
        ], 200);
    }

    /**
     * 上传私有文件（需要 ApiAuth 认证）
     *
     * 请求：POST /api/file/upload/private
     *
     * 注意：文件模块暂不支持私有文件概念，此方法与 uploadPublic 功能相同。
     * 为保持API一致性而保留此接口。
     * re_type和re_id仅用于上传时的类型验证，不保存到数据库。
     * 实际关联关系由业务API处理。
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadPrivate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file' => FileTypeConfig::fileRule(),
            're_type' => 'nullable|string|max:50',  // 仅用于验证，不保存
            're_id' => 'nullable|numeric|min:0',    // 仅用于验证，不保存
        ]);

        $userId = $request->attributes->get('user_id');
        if ($userId === null) {
            return response()->json([
                'success' => false,
                'message' => '未认证',
                'code' => 401,
            ], 401);
        }

        // 上传文件，不建立业务关联
        $fileModel = FileService::uploadFile(
            $validated['file'],
            (int) $userId,
            '',  // 不保存关联类型
            0    // 不保存关联ID
        );

        return response()->json([
            'success' => true,
            'message' => '文件上传成功。文件ID需传给业务API建立使用标记。',
            'data' => [
                'id' => $fileModel->id,
                'original_name' => $fileModel->o_name,
                'file_size' => $fileModel->fsize,
                'file_type' => $fileModel->type1,
                'url' => FileService::getFileUrl($fileModel->id),
                'status' => $fileModel->status,
                'created_at' => $fileModel->created_at->toIso8601String(),
            ],
            'code' => 200,
        ], 200);
    }

    /**
     * 上传文件到临时储存（需要 ApiAuth 认证）
     *
     * 请求：POST /api/file/upload/temp
     *
     * 接收 multipart/form-data 的 file 上传，保存到临时储存（不写数据库），返回相对路径 path
     * 支持文件类型：pdf, doc, docx, txt, xlsx, xls, ppt, pptx, csv
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadTemp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file' => FileTypeConfig::tempFileRule(),
        ]);

        $userId = $request->attributes->get('user_id');
        if ($userId === null) {
            return response()->json([
                'success' => false,
                'message' => '未认证',
                'code' => 401,
            ], 401);
        }

        $file = $validated['file'];
        $path = ModuleFileService::saveTempUploadFile($file);

        return response()->json([
            'success' => true,
            'message' => '文件已保存到临时储存',
            'code' => 200,
            'data' => [
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'ext' => $file->getClientOriginalExtension() ?: $file->guessExtension(),
            ],
        ], 200);
    }

    /**
     * 下载文件（公开访问）
     *
     * 请求：GET /api/v1/files/{id}/download
     *
     * @param int $id 文件ID
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|JsonResponse
     */
    public function download(int $id)
    {
        $result = FileService::downloadFile($id);

        if ($result === null) {
            return response()->json([
                'success' => false,
                'message' => '文件不存在',
                'code' => 404,
            ], 404);
        }

        return $result;
    }
}
