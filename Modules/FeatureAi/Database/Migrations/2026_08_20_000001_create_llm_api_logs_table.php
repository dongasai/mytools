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
        Schema::create('ai_llm_api_logs', function (Blueprint $table) {
            $table->id()->comment('主键ID');

            // 模型关联
            $table->unsignedBigInteger('provider_id')->comment('提供商ID');
            $table->unsignedBigInteger('model_id')->comment('模型ID');
            $table->string('model_name', 100)->comment('模型名称(冗余,便于查询)');

            // 请求标识
            $table->string('request_id', 100)->unique()->comment('请求ID(唯一,对应日志文件)');

            // 服务信息
            $table->string('service_type', 50)->comment('服务类型(如chat,image等)');
            $table->string('service_name', 100)->comment('服务名称(具体服务标识)');

            // Token统计
            $table->unsignedInteger('input_tokens')->default(0)->comment('输入tokens数量');
            $table->unsignedInteger('output_tokens')->default(0)->comment('输出tokens数量');
            $table->unsignedInteger('total_tokens')->default(0)->comment('总tokens数量');

            // 成本(高精度,单位:美元)
            $table->decimal('input_cost', 15, 10)->default(0.0000000000)->comment('输入成本');
            $table->decimal('output_cost', 15, 10)->default(0.0000000000)->comment('输出成本');
            $table->decimal('total_cost', 15, 10)->default(0.0000000000)->comment('总成本');

            // 性能与状态
            $table->unsignedInteger('response_time_ms')->default(0)->comment('响应时间(毫秒)');
            $table->boolean('success')->default(true)->comment('是否成功:1成功,0失败');
            $table->string('error_type', 50)->nullable()->comment('错误类型(失败时记录)');

            $table->timestamps();

            // 索引
            $table->index('provider_id', 'idx_provider_id');
            $table->index('model_id', 'idx_model_id');
            $table->index('model_name', 'idx_model_name');
            $table->index('service_type', 'idx_service_type');
            $table->index('service_name', 'idx_service_name');
            $table->index('success', 'idx_success');
            $table->index('error_type', 'idx_error_type');
            $table->index('created_at', 'idx_created_at');

            // 复合索引(用于统计分析)
            $table->index(['service_type', 'service_name'], 'idx_service');
            $table->index(['provider_id', 'created_at'], 'idx_provider_time');

            $table->comment('LLM API调用日志表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_llm_api_logs');
    }
};
