<?php

declare(strict_types=1);

namespace Modules\FeatureExcelDemo\Services;

use Illuminate\Support\Facades\DB;
use Modules\Application\Services\SystemLogService;
use Modules\FeatureExcelDemo\Models\DemoOrder;

/**
 * 演示订单服务
 *
 * 提供订单的增删改查和导入导出功能
 */
class DemoOrderService
{
    /**
     * 批量创建订单
     *
     * 整批使用单一事务包裹，行级失败收集错误但不影响其他行提交。
     * 错误信息中的行号来自Excel原始行号（_excel_row_number字段）。
     *
     * @param  array  $ordersData  订单数据数组，每行应包含 _excel_row_number 字段
     * @param  int  $enterpriseId  企业ID（租户ID）
     * @return array 创建结果 ['success' => int, 'failed' => int, 'errors' => array]
     */
    public static function batchCreate(array $ordersData, int $enterpriseId): array
    {
        $success = 0;
        $failed = 0;
        $errors = [];

        // 必需字段列表
        $requiredFields = ['order_no', 'customer_name', 'product_name', 'quantity', 'unit_price', 'order_date'];

        // 整批单一事务：所有成功行统一提交，失败行仅记录错误
        DB::beginTransaction();

        try {
            foreach ($ordersData as $orderData) {
                // 获取Excel原始行号（由模板transformRow注入）
                $rowNumber = $orderData['_excel_row_number'] ?? '未知';

                // 验证必需字段存在性
                $missingFields = [];
                foreach ($requiredFields as $field) {
                    if (! array_key_exists($field, $orderData)) {
                        $missingFields[] = $field;
                    }
                }
                if (! empty($missingFields)) {
                    $errors[] = "第{$rowNumber}行：缺少必需字段 ".implode(', ', $missingFields);
                    $failed++;

                    continue;
                }

                // 检查订单号是否已存在（限制在当前租户内）
                if (DemoOrder::where('enterprise_id', $enterpriseId)->where('order_no', $orderData['order_no'])->exists()) {
                    $errors[] = "第{$rowNumber}行：订单号 {$orderData['order_no']} 已存在";
                    $failed++;

                    continue;
                }

                // 注入商户ID
                $orderData['enterprise_id'] = $enterpriseId;

                // 创建订单
                DemoOrder::create($orderData);
                $success++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            SystemLogService::exception('feature_excel_demo', $e, [
                'enterprise_id' => $enterpriseId,
                'action' => 'batchCreate',
                'data_count' => count($ordersData),
            ]);

            return [
                'success' => 0,
                'failed' => count($ordersData),
                'errors' => ['系统错误：数据库操作失败，请稍后重试'],
            ];
        }

        return [
            'success' => $success,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    /**
     * 查询订单列表
     *
     * @param  int  $enterpriseId  企业ID（租户ID）
     * @param  array  $filters  过滤条件
     * @param  int  $page  页码
     * @param  int  $pageSize  每页数量
     * @return array 订单列表
     */
    public static function list(int $enterpriseId, array $filters = [], int $page = 1, int $pageSize = 20): array
    {
        $query = DemoOrder::query()->where('enterprise_id', $enterpriseId);

        // 状态过滤
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // 订单号搜索
        if (! empty($filters['order_no'])) {
            $query->where('order_no', 'like', "%{$filters['order_no']}%");
        }

        // 客户姓名搜索
        if (! empty($filters['customer_name'])) {
            $query->where('customer_name', 'like', "%{$filters['customer_name']}%");
        }

        // 日期范围
        if (! empty($filters['date_from'])) {
            $query->where('order_date', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->where('order_date', '<=', $filters['date_to']);
        }

        // 排序
        $query->orderBy('created_at', 'desc');

        // 分页
        $total = $query->count();
        $list = $query->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get()
            ->toArray();

        return [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
        ];
    }

    /**
     * 获取订单详情
     *
     * @param  int  $id  订单ID
     */
    public static function detail(int $id): ?DemoOrder
    {
        return DemoOrder::find($id);
    }

    /**
     * 更新订单状态
     *
     * @param  int  $id  订单ID
     * @param  string  $status  新状态
     */
    public static function updateStatus(int $id, string $status): bool
    {
        $order = DemoOrder::find($id);
        if (! $order) {
            return false;
        }

        $validStatuses = ['pending', 'confirmed', 'shipped', 'completed'];
        if (! in_array($status, $validStatuses)) {
            return false;
        }

        $order->status = $status;

        return $order->save();
    }

    /**
     * 删除订单
     *
     * @param  int  $id  订单ID
     */
    public static function delete(int $id): bool
    {
        $order = DemoOrder::find($id);
        if (! $order) {
            return false;
        }

        return $order->delete();
    }
}
