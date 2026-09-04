<?php

declare(strict_types=1);

namespace Modules\FeatureExcelDemo\ApiProto\Handlers;

use Modules\ApiProto\Handlers\BaseHandler;
use Modules\ApiProto\Protobuf\FeatureExcelDemo\Orderexcel\FeatureExcelDemoOrderExportRequest;
use Modules\ApiProto\Protobuf\FeatureExcelDemo\Orderexcel\FeatureExcelDemoOrderExportResponse;
use Modules\ApiProto\Protobuf\FeatureExcelDemo\Orderexcel\FeatureExcelDemoOrderExportResponse\ExportResult;
use Modules\FeatureExcel\Services\ModuleFeatureExcelService;
use Modules\FeatureExcelDemo\FeatureExcelTemplates\DemoOrderExportTemplate;
use Modules\FeatureExcelDemo\Services\DemoOrderService;

/**
 * 订单导出 Handler
 */
class OrderExportHandler extends BaseHandler
{
    protected bool $need_token = true;

    protected bool $need_login = true;

    /**
     * 处理订单导出
     *
     * @param  FeatureExcelDemoOrderExportRequest  $request
     * @return FeatureExcelDemoOrderExportResponse
     */
    public function handle(\Google\Protobuf\Internal\Message $request): \Google\Protobuf\Internal\Message
    {
        // 1. 构建过滤条件
        $filters = [];
        if ($request->getStatus() !== '') {
            $filters['status'] = $request->getStatus();
        }
        if ($request->getDateFrom() !== '') {
            $filters['date_from'] = $request->getDateFrom();
        }
        if ($request->getDateTo() !== '') {
            $filters['date_to'] = $request->getDateTo();
        }

        // 2. 查询数据（传入商户ID实现租户隔离）
        $result = DemoOrderService::list($this->token_enterprise_id, $filters, 1, 10000);
        $orders = $result['list'];

        if (empty($orders)) {
            return $this->errorResponse(
                new FeatureExcelDemoOrderExportResponse,
                404,
                '没有符合条件的订单数据'
            );
        }

        // 3. 实例化导出模板
        $template = new DemoOrderExportTemplate;

        // 4. 执行导出
        $fileUrl = ModuleFeatureExcelService::export($orders, $template, [
            'tenant_id' => $this->token_enterprise_id,
            'user_id' => $this->user_id,
            'variables' => ['date' => date('Ymd')],
        ]);

        // 5. 构建响应
        $response = new FeatureExcelDemoOrderExportResponse;

        $data = new ExportResult;
        $data->setFileUrl($fileUrl);
        $data->setTotal(count($orders));

        return $this->successResponse($response, $data, '导出成功');
    }
}
