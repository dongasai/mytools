<?php

declare(strict_types=1);

namespace Modules\FeatureExcelDemo\ApiProto\Handlers;

use Modules\ApiProto\Handlers\BaseHandler;
use Modules\ApiProto\Protobuf\FeatureExcelDemo\Orderexcel\FeatureExcelDemoOrderDownloadTemplateRequest;
use Modules\ApiProto\Protobuf\FeatureExcelDemo\Orderexcel\FeatureExcelDemoOrderDownloadTemplateResponse;
use Modules\ApiProto\Protobuf\FeatureExcelDemo\Orderexcel\FeatureExcelDemoOrderDownloadTemplateResponse\TemplateResult;

/**
 * 下载导入模板 Handler
 *
 * 返回已生成的订单导入 Excel 模板文件下载地址
 */
class OrderDownloadTemplateHandler extends BaseHandler
{
    protected bool $need_token = true;

    protected bool $need_login = true;

    /**
     * 处理模板下载请求
     *
     * @param  FeatureExcelDemoOrderDownloadTemplateRequest  $request
     * @return FeatureExcelDemoOrderDownloadTemplateResponse
     */
    public function handle(\Google\Protobuf\Internal\Message $request): \Google\Protobuf\Internal\Message
    {
        // 模板文件路径（由 php artisan featureexcel:generate-template 命令生成）
        $templateFile = 'excel_templates/演示订单导入模板.xlsx';
        $absolutePath = public_path($templateFile);

        // 检查模板文件是否存在
        if (! file_exists($absolutePath)) {
            return $this->errorResponse(
                new FeatureExcelDemoOrderDownloadTemplateResponse,
                404,
                '模板文件不存在，请先运行命令生成：php artisan featureexcel:generate-template DemoOrderImportTemplate'
            );
        }

        // 生成下载 URL
        $fileUrl = url($templateFile);
        $fileSize = filesize($absolutePath);

        // 获取字段数量（从模板定义）
        $template = new \Modules\FeatureExcelDemo\FeatureExcelTemplates\DemoOrderImportTemplate;
        $fieldCount = count($template->getFields());

        // 构建响应
        $response = new FeatureExcelDemoOrderDownloadTemplateResponse;

        $data = new TemplateResult;
        $data->setFileUrl($fileUrl);
        $data->setFileName('演示订单导入模板.xlsx');
        $data->setFileSize($fileSize);
        $data->setFieldCount($fieldCount);

        return $this->successResponse($response, $data, '模板地址获取成功');
    }
}
