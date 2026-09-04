<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ai_tests', function (Blueprint $table) {
            $table->id()->comment('主键ID');

            // 关联字段
            $table->unsignedBigInteger('provider_id')->comment('提供商ID');
            $table->unsignedBigInteger('model_id')->comment('模型ID');

            // 测试信息
            $table->string('test_type', 50)->comment('测试类型:connect/response/cost/image');
            $table->string('test_name', 100)->comment('测试名称');
            $table->json('test_config_json')->nullable()->comment('测试配置(JSON格式)');

            // 状态
            $table->unsignedTinyInteger('status')->default(1)->comment('状态:1待执行,2执行中,3成功,4失败');

            // 测试统计
            $table->unsignedInteger('total_tests')->default(1)->comment('总测试次数');
            $table->unsignedInteger('success_count')->default(0)->comment('成功次数');
            $table->unsignedInteger('fail_count')->default(0)->comment('失败次数');
            $table->unsignedInteger('avg_response_time_ms')->default(0)->comment('平均响应时间(毫秒)');

            $table->timestamps();

            // 索引
            $table->index('provider_id', 'idx_provider_id');
            $table->index('model_id', 'idx_model_id');
            $table->index('test_type', 'idx_test_type');
            $table->index('status', 'idx_status');

            $table->comment('AI集成测试记录表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_tests');
    }
};
