<?php

declare(strict_types=1);

namespace Modules\FeatureExcelDemo\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\FeatureExcelDemo\Models\DemoOrder;

class DemoOrderSeeder extends Seeder
{
    /**
     * 运行数据库填充
     *
     * 测试数据归属企业ID 11（与 php artisan enterprise:token 默认用户11一致）
     */
    public function run(): void
    {
        $orders = [
            [
                'enterprise_id' => 11,
                'order_no' => 'ORD1234567890',
                'customer_name' => '张三',
                'product_name' => '产品A',
                'quantity' => 10,
                'unit_price' => 100.00,
                'total_amount' => 1000.00,
                'order_date' => '2026-08-01',
                'status' => 'completed',
                'remark' => '测试订单1',
            ],
            [
                'enterprise_id' => 11,
                'order_no' => 'ORD1234567891',
                'customer_name' => '李四',
                'product_name' => '产品B',
                'quantity' => 5,
                'unit_price' => 200.00,
                'total_amount' => 1000.00,
                'order_date' => '2026-08-02',
                'status' => 'confirmed',
                'remark' => '测试订单2',
            ],
            [
                'enterprise_id' => 11,
                'order_no' => 'ORD1234567892',
                'customer_name' => '王五',
                'product_name' => '产品C',
                'quantity' => 20,
                'unit_price' => 50.00,
                'total_amount' => 1000.00,
                'order_date' => '2026-08-03',
                'status' => 'shipped',
                'remark' => '测试订单3',
            ],
            [
                'enterprise_id' => 11,
                'order_no' => 'ORD1234567893',
                'customer_name' => '赵六',
                'product_name' => '产品D',
                'quantity' => 8,
                'unit_price' => 125.00,
                'total_amount' => 1000.00,
                'order_date' => '2026-08-04',
                'status' => 'pending',
                'remark' => '测试订单4',
            ],
            [
                'enterprise_id' => 11,
                'order_no' => 'ORD1234567894',
                'customer_name' => '钱七',
                'product_name' => '产品E',
                'quantity' => 15,
                'unit_price' => 66.67,
                'total_amount' => 1000.05,
                'order_date' => '2026-08-05',
                'status' => 'completed',
                'remark' => '测试订单5',
            ],
        ];

        foreach ($orders as $order) {
            DemoOrder::create($order);
        }

        $this->command->info('成功创建 ' . count($orders) . ' 条测试订单数据');
    }
}