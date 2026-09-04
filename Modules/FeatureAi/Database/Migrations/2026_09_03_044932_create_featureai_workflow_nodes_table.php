<?php

/**
 * Workflow节点表迁移
 *
 * 存储AI Agent工作流的节点信息
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * 创建featureai_workflow_nodes表
     * 用于存储工作流节点，定义工作流中每个步骤的配置和属性
     */
    public function up(): void
    {
        if (!Schema::hasTable('featureai_workflow_nodes')) {
            Schema::create('featureai_workflow_nodes', function (Blueprint $table) {
                $table->id()->comment('主键ID');

                // 外键字段
                $table->unsignedBigInteger('workflow_id')->comment('所属工作流ID');
                $table->unsignedBigInteger('agent_id')->nullable()->comment('关联Agent ID');

                // 节点字段
                $table->string('node_name', 100)->comment('节点名称');
                $table->unsignedTinyInteger('node_type')->default(2)->comment('节点类型:1开始节点,2处理节点,3决策节点,4结束节点');
                $table->string('input_event', 255)->nullable()->comment('输入事件');
                $table->json('output_events')->nullable()->comment('输出事件列表');
                $table->json('config')->nullable()->comment('节点配置JSON');
                $table->integer('order_index')->default(0)->comment('排序索引');
                $table->boolean('is_enabled')->default(true)->comment('是否启用:1启用,0禁用');

                $table->timestamps();

                // 索引
                $table->index('workflow_id', 'idx_workflow_id');
                $table->index('agent_id', 'idx_agent_id');
                $table->index(['workflow_id', 'order_index'], 'idx_workflow_order');
                $table->index('node_type', 'idx_node_type');

                // 外键约束
                $table->foreign('workflow_id', 'fk_nodes_workflow')
                    ->references('id')
                    ->on('featureai_workflow_definitions')
                    ->onDelete('cascade');

                $table->foreign('agent_id', 'fk_nodes_agent')
                    ->references('id')
                    ->on('featureai_dynamic_agents')
                    ->onDelete('set null');

                $table->comment('FeatureAi模块-工作流节点表');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * 删除featureai_workflow_nodes表
     */
    public function down(): void
    {
        Schema::dropIfExists('featureai_workflow_nodes');
    }
};
