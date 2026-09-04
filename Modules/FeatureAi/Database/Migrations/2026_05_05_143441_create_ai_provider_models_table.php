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
        Schema::create('ai_provider_models', function (Blueprint $table) {
            $table->id()->comment('主键ID');

            // 关联字段
            $table->unsignedBigInteger('provider_id')->comment('提供商ID(关联ai_providers.id)');

            // 模型基础信息
            $table->string('model_name', 100)->comment('模型名称:gpt-4/claude-3-opus等');
            $table->string('model_type', 50)->comment('模型类型:chat/image/embedding');
            $table->unsignedInteger('max_tokens')->default(4096)->comment('最大tokens限制');

            // 成本配置
            $table->decimal('cost_per_input_token', 10, 8)->default(0.00000000)->comment('输入tokens单价(美元)');
            $table->decimal('cost_per_output_token', 10, 8)->default(0.00000000)->comment('输出tokens单价(美元)');

            // 状态
            $table->unsignedTinyInteger('is_active')->default(1)->comment('是否启用:1启用,2禁用');

            // 配置参数
            $table->json('config_json')->nullable()->comment('模型特定配置(JSON格式)');

            $table->timestamps();
            $table->softDeletes()->comment('软删除时间');

            // 索引
            $table->index('provider_id', 'idx_model_provider_id');
            $table->index('model_name', 'idx_model_name');
            $table->index('model_type', 'idx_model_type');
            $table->index('is_active', 'idx_model_is_active');
            $table->index('deleted_at', 'idx_model_deleted_at');

            $table->comment('AI模型配置表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_provider_models');
    }
};
