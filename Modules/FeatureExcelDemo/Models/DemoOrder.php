<?php

declare(strict_types=1);

namespace Modules\FeatureExcelDemo\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 演示订单模型
 *
 * 用于演示 FeatureExcel 导入导出功能
 */
class DemoOrder extends Model
{
    protected $table = 'demo_orders';

    /**
     * 可填充字段
     */
    protected $fillable = [
        'enterprise_id',
        'order_no',
        'customer_name',
        'product_name',
        'quantity',
        'unit_price',
        'total_amount',
        'order_date',
        'status',
        'remark',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'order_date' => 'date',
    ];
}
