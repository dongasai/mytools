<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 创建Workflow节点表.
 *
 * 存储工作流的节点定义，包括节点类型、关联Agent、事件配置等
 */
return new class extends Migration
{
    /**
     * 运行迁移.
     */
    public function up(): void
    {
        if (Schema::hasTable('featureai_workflow_nodes')) {
            return;
        }

        Schema::create('featureai_workflow_nodes', function (Blueprint $table) {
            $table->id()->comment('主键ID');

            // 外键字段
            $table->unsignedBigInteger('workflow_id')->comment('关联Workflow定义ID');
            $table->unsignedBigInteger('agent_id')->nullable()->comment('关联Dynamic Agent ID');

            // 节点信息
            $table->string('node_name', 100)->comment('节点名称');
            $table->enum('node_type', ['start', 'process', 'decision', 'end'])->comment('节点类型:start开始,process处理,decision决策,end结束');
            $table->string('input_event', 255)->nullable()->comment('输入事件类型');
            $table->json('output_events')->nullable()->comment('输出事件类型');
            $table->json('config')->nullable()->comment('节点配置');
            $table->integer('order_index')->default(0)->comment('执行顺序');
            $table->boolean('is_enabled')->default(true)->comment('是否启用');

            $table->timestamps();

            // 索引
            $table->index('workflow_id', 'idx_workflow_id');
            $table->index('agent_id', 'idx_agent_id');
            $table->index('node_type', 'idx_node_type');
            $table->index('is_enabled', 'idx_is_enabled');
            $table->index(['workflow_id', 'order_index'], 'idx_workflow_order');

            // 外键约束
            $table->foreign('workflow_id')
                ->references('id')
                ->on('featureai_workflow_definitions')
                ->onDelete('cascade');

            $table->foreign('agent_id')
                ->references('id')
                ->on('featureai_dynamic_agents')
                ->onDelete('set null');
        });

        // 添加表注释
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `featureai_workflow_nodes` COMMENT='Workflow节点表'");
        }
    }

    /**
     * 回滚迁移.
     */
    public function down(): void
    {
        Schema::dropIfExists('featureai_workflow_nodes');
    }
};
