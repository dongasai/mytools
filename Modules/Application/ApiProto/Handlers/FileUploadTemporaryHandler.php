<?php

declare(strict_types=1);

namespace Modules\Application\ApiProto\Handlers;

use Modules\ApiProto\Handlers\BaseHandler;
use Google\Protobuf\Internal\Message;
use Modules\ApiProto\Protobuf\Application\File\ApplicationFileUploadTemporaryResponse;
use Modules\ApiProto\Protobuf\Application\File\ApplicationFileUploadTemporaryData;
use Modules\ApiProto\Protobuf\ApiProto\Common\StatusCode;
use Modules\AFile\Services\TemporaryService;

/**
 * 临时文件上传 Handler
 *
 * 处理临时文件上传请求
 * Api Path: /api/proto/application/file/upload_temporary
 */
class FileUploadTemporaryHandler extends BaseHandler
{
    protected bool $need_login = true;

    /**
     * 处理临时文件上传请求
     */
    public function handle(Message $request): Message
    {
        $fileData = $request->getFileData();
        $fileExt = $request->getFileExt();

        // 参数验证
        if (empty($fileData)) {
            $response = new ApplicationFileUploadTemporaryResponse();
            return $this->errorResponse($response, StatusCode::STATUS_CODE_VALIDATE_ERROR, '文件内容不能为空');
        }

        if (empty($fileExt)) {
            $response = new ApplicationFileUploadTemporaryResponse();
            return $this->errorResponse($response, StatusCode::STATUS_CODE_VALIDATE_ERROR, '文件扩展名不能为空');
        }

        // TemporaryService 是实例方法，需通过容器获取
        $tempService = app(TemporaryService::class);

        try {
            $filePath = $tempService->save($fileExt, $fileData);
            $fileUrl = $tempService->getDownUrl($filePath);

            $uploadData = new ApplicationFileUploadTemporaryData();
            $uploadData->setFilePath($filePath);
            $uploadData->setFileUrl($fileUrl);

            $response = new ApplicationFileUploadTemporaryResponse();
            return $this->successResponse($response, $uploadData, '临时文件上传成功');
        } catch (\Exception $e) {
            $response = new ApplicationFileUploadTemporaryResponse();
            return $this->errorResponse($response, StatusCode::STATUS_CODE_INTERNAL_ERROR, '临时文件上传失败：' . $e->getMessage());
        }
    }
}