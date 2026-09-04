<?php

/**
 * 创建动态Agent执行记录表
 *
 * 用于记录Agent的执行历史、输入输出和性能指标
 */

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
        // 创建 featureai_dynamic_agent_executions 表
        if (!Schema::hasTable('featureai_dynamic_agent_executions')) {
            Schema::create('featureai_dynamic_agent_executions', function (Blueprint $table) {
                // 主键
                $table->id();

                // 外键关联
                $table->foreignId('agent_id')
                    ->nullable()
                    ->constrained('featureai_dynamic_agents')
                    ->onDelete('set null')
                    ->comment('关联的Agent ID');

                $table->foreignId('workflow_id')
                    ->nullable()
                    ->constrained('featureai_workflow_executions')
                    ->onDelete('cascade')
                    ->comment('关联的工作流执行ID');

                // 执行状态
                $table->string('status')->comment('执行状态');

                // 输入输出
                $table->json('input')->comment('输入消息');
                $table->json('output')->nullable()->comment('输出结果');

                // 工具调用记录
                $table->json('tools_called')->nullable()->comment('调用的工具');

                // 性能指标
                $table->integer('tokens_used')->nullable()->comment('使用的token数');
                $table->integer('duration_ms')->nullable()->comment('执行时长(毫秒)');

                // 错误信息
                $table->text('error')->nullable()->comment('错误信息');

                // 时间戳
                $table->timestamps();

                // 索引
                $table->index('agent_id');
                $table->index('workflow_id');
                $table->index('status');
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('featureai_dynamic_agent_executions');
    }
};