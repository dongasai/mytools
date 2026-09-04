<?php

declare(strict_types=1);

namespace Modules\FeatureExcelDemo\ApiProto\Handlers;

use Modules\ApiProto\Handlers\BaseHandler;
use Modules\ApiProto\Protobuf\FeatureExcelDemo\Orderexcel\FeatureExcelDemoOrderImportRequest;
use Modules\ApiProto\Protobuf\FeatureExcelDemo\Orderexcel\FeatureExcelDemoOrderImportResponse;
use Modules\ApiProto\Protobuf\FeatureExcelDemo\Orderexcel\FeatureExcelDemoOrderImportResponse\ImportResult;
use Modules\FeatureExcel\Services\ModuleFeatureExcelService;
use Modules\FeatureExcelDemo\FeatureExcelTemplates\DemoOrderImportTemplate;
use Modules\FeatureExcelDemo\Services\DemoOrderService;

/**
 * 订单导入 Handler
 */
class OrderImportHandler extends BaseHandler
{
    protected bool $need_token = true;

    protected bool $need_login = true;

    /**
     * 处理订单导入
     *
     * @param  FeatureExcelDemoOrderImportRequest  $request
     * @return FeatureExcelDemoOrderImportResponse
     */
    public function handle(\Google\Protobuf\Internal\Message $request): \Google\Protobuf\Internal\Message
    {
        $filePath = $request->getFilePath();

        // 1. 实例化导入模板
        $template = new DemoOrderImportTemplate;

        // 2. 执行导入（含验证）
        $result = ModuleFeatureExcelService::importWithValidation($filePath, $template);

        if (! $result->isSuccess()) {
            // 错误结构为二维数组（行索引 → 错误消息数组），需展平为字符串列表
            $errors = [];
            foreach ($result->getErrors() as $rowErrors) {
                foreach ($rowErrors as $errorMsg) {
                    $errors[] = $errorMsg;
                }
            }

            return $this->errorResponse(
                new FeatureExcelDemoOrderImportResponse,
                400,
                '导入失败：'.implode('; ', $errors)
            );
        }

        // 3. 批量创建订单（传入商户ID实现租户隔离）
        $createResult = DemoOrderService::batchCreate($result->getData(), $this->token_enterprise_id);

        // 4. 构建响应
        $response = new FeatureExcelDemoOrderImportResponse;

        $data = new ImportResult;
        $data->setSuccessCount($createResult['success']);
        $data->setFailedCount($createResult['failed']);
        foreach ($createResult['errors'] as $error) {
            $data->getErrors()[] = $error;
        }

        // 全部失败时返回错误响应
        if ($createResult['success'] === 0 && $createResult['failed'] > 0) {
            $response->setData($data);

            return $this->errorResponse(
                $response,
                400,
                '导入失败：全部 '.$createResult['failed'].' 条记录导入失败'
            );
        }

        // 部分失败时返回成功但提示部分失败
        if ($createResult['failed'] > 0) {
            return $this->successResponse(
                $response,
                $data,
                '部分导入成功：'.$createResult['success'].' 条成功，'.$createResult['failed'].' 条失败'
            );
        }

        return $this->successResponse($response, $data, '导入成功');
    }
}
