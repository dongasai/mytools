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
        Schema::create('ai_providers', function (Blueprint $table) {
            $table->id()->comment('主键ID');

            // 提供商基础信息
            $table->string('provider_type', 50)->comment('提供商类型:openai/claude/gemini/custom');
            $table->string('provider_name', 100)->comment('提供商名称');
            $table->string('api_key', 255)->comment('API密钥(加密存储)');
            $table->string('api_endpoint', 255)->nullable()->comment('API端点URL');

            // 状态和优先级
            $table->unsignedTinyInteger('is_active')->default(1)->comment('是否启用:1启用,2禁用');
            $table->unsignedTinyInteger('priority')->default(0)->comment('优先级:数字越大优先级越高');

            // 配置参数
            $table->json('config_json')->nullable()->comment('其他配置参数(JSON格式)');

            $table->timestamps();
            $table->softDeletes()->comment('软删除时间');

            // 索引
            $table->index('provider_type', 'idx_provider_type');
            $table->index('is_active', 'idx_is_active');
            $table->index('deleted_at', 'idx_deleted_at');

            $table->comment('AI服务提供商配置表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_providers');
    }
};
