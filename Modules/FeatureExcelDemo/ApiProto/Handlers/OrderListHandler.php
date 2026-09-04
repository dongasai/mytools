<?php

declare(strict_types=1);

namespace Modules\FeatureExcelDemo\ApiProto\Handlers;

use Modules\ApiProto\Handlers\BaseHandler;
use Modules\ApiProto\Protobuf\FeatureExcelDemo\Orderexcel\FeatureExcelDemoOrderListRequest;
use Modules\ApiProto\Protobuf\FeatureExcelDemo\Orderexcel\FeatureExcelDemoOrderListResponse;
use Modules\ApiProto\Protobuf\FeatureExcelDemo\Orderexcel\FeatureExcelDemoOrderListResponse\ListResult;
use Modules\ApiProto\Protobuf\FeatureExcelDemo\Orderexcel\FeatureExcelDemoOrderListResponse\OrderItem;
use Modules\FeatureExcelDemo\Services\DemoOrderService;

/**
 * 订单列表 Handler
 */
class OrderListHandler extends BaseHandler
{
    protected bool $need_token = true;

    protected bool $need_login = true;

    /**
     * 处理订单列表查询
     *
     * @param  FeatureExcelDemoOrderListRequest  $request
     * @return FeatureExcelDemoOrderListResponse
     */
    public function handle(\Google\Protobuf\Internal\Message $request): \Google\Protobuf\Internal\Message
    {
        // 1. 构建过滤条件
        $filters = [];
        if ($request->getStatus() !== '') {
            $filters['status'] = $request->getStatus();
        }
        if ($request->getOrderNo() !== '') {
            $filters['order_no'] = $request->getOrderNo();
        }
        if ($request->getCustomerName() !== '') {
            $filters['customer_name'] = $request->getCustomerName();
        }
        if ($request->getDateFrom() !== '') {
            $filters['date_from'] = $request->getDateFrom();
        }
        if ($request->getDateTo() !== '') {
            $filters['date_to'] = $request->getDateTo();
        }

        // 分页参数边界校验
        $page = $request->getPage() !== 0 ? $request->getPage() : 1;
        $pageSize = $request->getPageSize() !== 0 ? $request->getPageSize() : 20;

        // page最小值为1
        if ($page < 1) {
            $page = 1;
        }

        // pageSize限制范围1-100
        if ($pageSize < 1) {
            $pageSize = 1;
        } elseif ($pageSize > 100) {
            $pageSize = 100;
        }

        // 2. 查询数据（传入商户ID实现租户隔离）
        $result = DemoOrderService::list($this->token_enterprise_id, $filters, $page, $pageSize);

        // 3. 构建响应
        $response = new FeatureExcelDemoOrderListResponse;

        $data = new ListResult;
        $data->setTotal($result['total']);
        $data->setPage($result['page']);
        $data->setPageSize($result['page_size']);

        foreach ($result['list'] as $order) {
            $item = new OrderItem;
            $item->setId($order['id']);
            $item->setOrderNo($order['order_no']);
            $item->setCustomerName($order['customer_name']);
            $item->setProductName($order['product_name']);
            $item->setQuantity($order['quantity']);
            $item->setUnitPrice($order['unit_price']);
            $item->setTotalAmount($order['total_amount']);
            $item->setOrderDate($order['order_date']);
            $item->setStatus($order['status']);
            $item->setRemark($order['remark'] ?? '');
            $item->setCreatedAt($order['created_at']);

            $data->getList()[] = $item;
        }

        return $this->successResponse($response, $data, '查询成功');
    }
}
