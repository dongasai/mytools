<?php

/**
 * Workflow执行记录表迁移
 *
 * 存储AI Agent工作流的执行记录
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * 创建featureai_workflow_executions表
     * 用于记录工作流的执行状态和历史
     */
    public function up(): void
    {
        if (!Schema::hasTable('featureai_workflow_executions')) {
            Schema::create('featureai_workflow_executions', function (Blueprint $table) {
                $table->id()->comment('主键ID');

                // 外键字段
                $table->unsignedBigInteger('workflow_id')->comment('所属工作流ID');

                // 执行记录字段
                $table->string('current_node', 255)->nullable()->comment('当前节点标识');
                $table->json('state')->nullable()->comment('执行状态数据JSON');
                $table->json('history')->nullable()->comment('执行历史记录JSON');
                $table->unsignedTinyInteger('status')->default(1)->comment('执行状态:1待执行,2执行中,3已完成,4失败,5已中断');

                $table->timestamps();

                // 索引
                $table->index('workflow_id', 'idx_workflow_id');
                $table->index('status', 'idx_status');
                $table->index(['workflow_id', 'status'], 'idx_workflow_status');
                $table->index('created_at', 'idx_created_at');

                // 外键约束
                $table->foreign('workflow_id', 'fk_executions_workflow')
                    ->references('id')
                    ->on('featureai_workflow_definitions')
                    ->onDelete('cascade');

                $table->comment('FeatureAi模块-工作流执行记录表');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * 删除featureai_workflow_executions表
     */
    public function down(): void
    {
        Schema::dropIfExists('featureai_workflow_executions');
    }
};
