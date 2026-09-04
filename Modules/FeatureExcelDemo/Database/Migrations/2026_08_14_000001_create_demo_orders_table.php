<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 创建演示订单表
 *
 * 用于 FeatureExcelDemo 模块的演示订单数据表，存储导入的订单数据
 *
 * 字段说明：
 * - id: 主键自增
 * - enterprise_id: 企业ID（租户ID），用于SaaS多租户数据隔离
 * - order_no: 订单号，唯一索引
 * - customer_name: 客户姓名
 * - product_name: 产品名称
 * - quantity: 数量
 * - unit_price: 单价
 * - total_amount: 总金额
 * - order_date: 订单日期
 * - status: 状态（pending/confirmed/shipped/completed）
 * - remark: 备注
 * - created_at/updated_at: 时间戳
 *
 * 索引说明：
 * - idx_enterprise_id: 商户ID索引（租户隔离查询）
 * - idx_enterprise_status: 企业ID+状态组合索引（常用查询优化）
 * - order_no: 唯一索引
 * - order_date: 日期范围查询
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demo_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('enterprise_id')->comment('企业ID（租户ID）');
            $table->string('order_no', 50)->unique()->comment('订单号');
            $table->string('customer_name', 100)->comment('客户姓名');
            $table->string('product_name', 200)->comment('产品名称');
            $table->integer('quantity')->default(1)->comment('数量');
            $table->decimal('unit_price', 10, 2)->comment('单价');
            $table->decimal('total_amount', 12, 2)->comment('总金额');
            $table->date('order_date')->comment('订单日期');
            $table->enum('status', ['pending', 'confirmed', 'shipped', 'completed'])->default('pending')->comment('状态');
            $table->text('remark')->nullable()->comment('备注');
            $table->timestamps();

            // 租户隔离索引
            $table->index('enterprise_id', 'idx_enterprise_id');
            $table->index(['enterprise_id', 'status'], 'idx_enterprise_status');

            $table->index('order_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_orders');
    }
};
