<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 创建Workflow执行记录表.
 *
 * 存储工作流的执行实例，包括当前状态、执行历史和执行状态
 */
return new class extends Migration
{
    /**
     * 运行迁移.
     */
    public function up(): void
    {
        if (Schema::hasTable('featureai_workflow_executions')) {
            return;
        }

        Schema::create('featureai_workflow_executions', function (Blueprint $table) {
            $table->id()->comment('主键ID');

            // 外键字段
            $table->unsignedBigInteger('workflow_id')->comment('关联Workflow定义ID');

            // 执行信息
            $table->string('current_node', 255)->nullable()->comment('当前节点名称');
            $table->json('state')->nullable()->comment('Workflow状态数据');
            $table->json('history')->nullable()->comment('执行历史记录');
            $table->enum('status', ['pending', 'running', 'completed', 'failed', 'interrupted'])
                ->default('pending')
                ->comment('执行状态:pending待执行,running执行中,completed完成,failed失败,interrupted中断');

            $table->timestamps();

            // 索引
            $table->index('workflow_id', 'idx_workflow_id');
            $table->index('status', 'idx_status');
            $table->index(['workflow_id', 'status'], 'idx_workflow_status');
            $table->index('created_at', 'idx_created_at');

            // 外键约束
            $table->foreign('workflow_id')
                ->references('id')
                ->on('featureai_workflow_definitions')
                ->onDelete('cascade');
        });

        // 添加表注释
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `featureai_workflow_executions` COMMENT='Workflow执行记录表'");
        }
    }

    /**
     * 回滚迁移.
     */
    public function down(): void
    {
        Schema::dropIfExists('featureai_workflow_executions');
    }
};
