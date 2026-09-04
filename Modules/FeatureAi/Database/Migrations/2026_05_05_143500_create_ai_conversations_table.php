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
        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->id()->comment('主键ID');

            // 关联字段
            $table->unsignedBigInteger('provider_id')->comment('提供商ID');
            $table->unsignedBigInteger('model_id')->comment('模型ID');
            $table->unsignedBigInteger('user_id')->nullable()->comment('用户ID(后台测试时可为NULL)');
            $table->string('conversation_id', 100)->comment('对话会话ID(多轮对话标识)');

            // 对话内容
            $table->text('prompt_text')->comment('用户输入的提示文本');
            $table->text('response_text')->nullable()->comment('AI返回的响应文本');

            // tokens统计
            $table->unsignedInteger('input_tokens')->default(0)->comment('输入tokens数量');
            $table->unsignedInteger('output_tokens')->default(0)->comment('输出tokens数量');

            // 成本
            $table->decimal('total_cost', 10, 6)->default(0.000000)->comment('总成本(美元)');

            // 状态和性能
            $table->unsignedTinyInteger('status')->default(1)->comment('状态:1进行中,2已完成,3失败');
            $table->text('error_message')->nullable()->comment('错误信息(失败时记录)');
            $table->unsignedInteger('response_time_ms')->default(0)->comment('响应时间(毫秒)');

            // 上下文
            $table->json('context_json')->nullable()->comment('对话上下文(JSON格式)');

            $table->timestamps();

            // 索引
            $table->index('provider_id', 'idx_provider_id');
            $table->index('model_id', 'idx_model_id');
            $table->index('user_id', 'idx_user_id');
            $table->index('conversation_id', 'idx_conversation_id');
            $table->index('status', 'idx_status');
            $table->index('created_at', 'idx_created_at');

            $table->comment('AI对话记录表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_conversations');
    }
};
