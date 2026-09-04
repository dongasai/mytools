<?php

declare(strict_types=1);

namespace Modules\Application\ApiProto\Handlers;

use Modules\ApiProto\Handlers\BaseHandler;
use Google\Protobuf\Internal\Message;
use Modules\ApiProto\Protobuf\Application\File\ApplicationFileUploadResponse;
use Modules\ApiProto\Protobuf\Application\File\ApplicationFileUploadData;
use Modules\ApiProto\Protobuf\ApiProto\Common\StatusCode;
use Modules\AFile\Services\FileService;
use Illuminate\Http\UploadedFile;

/**
 * 文件上传 Handler
 *
 * 处理文件上传请求（图片/视频）
 * Api Path: /api/proto/application/file/upload
 */
class FileUploadHandler extends BaseHandler
{
    protected bool $need_login = true;

    /**
     * 处理文件上传请求
     */
    public function handle(Message $request): Message
    {
        $userId = (int) $this->user_id;
        $fileData = $request->getFileData();
        $fileName = $request->getFileName();
        $fileType = $request->getFileType();
        $private = $request->getPrivate();

        // 参数验证
        if (empty($fileData)) {
            $response = new ApplicationFileUploadResponse();
            return $this->errorResponse($response, StatusCode::STATUS_CODE_VALIDATE_ERROR, '文件内容不能为空');
        }

        if (empty($fileName)) {
            $response = new ApplicationFileUploadResponse();
            return $this->errorResponse($response, StatusCode::STATUS_CODE_VALIDATE_ERROR, '文件名不能为空');
        }

        // 获取文件扩展名
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        if (empty($extension)) {
            $response = new ApplicationFileUploadResponse();
            return $this->errorResponse($response, StatusCode::STATUS_CODE_VALIDATE_ERROR, '文件名必须包含扩展名');
        }

        // 构造临时文件
        $tempPath = sys_get_temp_dir() . '/' . $fileName;
        file_put_contents($tempPath, $fileData);

        // 根据文件类型确定 MIME 类型
        $mimeType = $this->getMimeType($fileType, $extension);

        // 构造 UploadedFile 对象
        $uploadedFile = new UploadedFile(
            $tempPath,
            $fileName,
            $mimeType,
            null,
            true // test mode
        );

        // 调用 AFile Service 上传
        try {
            if ($fileType === 'image') {
                $file = FileService::uploadImage($uploadedFile, $userId, $private);
                $fileId = $file->id;
                $fileUrl = $file->url;
                $filePath = $file->path;
            } else {
                $file = FileService::uploadFile($uploadedFile, $userId);
                $fileId = $file->id;
                $fileUrl = FileService::getFileUrl($fileId);
                $filePath = $file->path;
            }

            // 删除临时文件
            @unlink($tempPath);

            $uploadData = new ApplicationFileUploadData();
            $uploadData->setFileId($fileId);
            $uploadData->setFileUrl($fileUrl);
            $uploadData->setFilePath($filePath);

            $response = new ApplicationFileUploadResponse();
            return $this->successResponse($response, $uploadData, '文件上传成功');
        } catch (\Exception $e) {
            // 删除临时文件
            @unlink($tempPath);

            $response = new ApplicationFileUploadResponse();
            return $this->errorResponse($response, StatusCode::STATUS_CODE_INTERNAL_ERROR, '文件上传失败：' . $e->getMessage());
        }
    }

    /**
     * 根据 file_type 和扩展名获取 MIME 类型
     *
     * @param string $fileType 文件类型（image/video/file）
     * @param string $extension 文件扩展名
     * @return string
     */
    private function getMimeType(string $fileType, string $extension): string
    {
        $mimeTypes = [
            'image' => [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
            ],
            'video' => [
                'mp4' => 'video/mp4',
                'avi' => 'video/x-msvideo',
                'mov' => 'video/quicktime',
                'wmv' => 'video/x-ms-wmv',
            ],
            'file' => [
                'pdf' => 'application/pdf',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'xls' => 'application/vnd.ms-excel',
                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ],
        ];

        return $mimeTypes[$fileType][$extension] ?? 'application/octet-stream';
    }
}