<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 创建动态Agent执行记录表.
 */
return new class extends Migration
{
    /**
     * 运行迁移.
     */
    public function up(): void
    {
        if (Schema::hasTable('featureai_dynamic_agent_executions')) {
            return;
        }

        Schema::create('featureai_dynamic_agent_executions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id')->comment('Agent ID');
            $table->string('workflow_id', 100)->nullable()->comment('NeuronAI Workflow ID');
            $table->unsignedBigInteger('user_id')->nullable()->comment('执行用户ID');
            $table->text('input_message')->nullable()->comment('输入消息');
            $table->text('output_message')->nullable()->comment('输出消息');
            $table->enum('status', ['success', 'failed', 'interrupted'])->comment('执行状态');
            $table->json('tools_called')->nullable()->comment('调用的工具列表');
            $table->integer('duration_ms')->nullable()->comment('执行时长（毫秒）');
            $table->timestamp('created_at')->nullable()->comment('创建时间');

            // 外键约束
            $table->foreign('agent_id', 'fk_executions_agent_id')
                ->references('id')
                ->on('featureai_dynamic_agents')
                ->onDelete('cascade');

            // 索引
            $table->index('agent_id', 'idx_execution_agent_id');
            $table->index('workflow_id', 'idx_workflow_id');
            $table->index('user_id', 'idx_user_id');
            $table->index('status', 'idx_status');
            $table->index('created_at', 'idx_created_at');
            $table->index(['agent_id', 'created_at'], 'idx_agent_created');
        });

        // 添加表注释
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `featureai_dynamic_agent_executions` COMMENT='Agent执行记录表'");
        }
    }

    /**
     * 回滚迁移.
     */
    public function down(): void
    {
        Schema::dropIfExists('featureai_dynamic_agent_executions');
    }
};
