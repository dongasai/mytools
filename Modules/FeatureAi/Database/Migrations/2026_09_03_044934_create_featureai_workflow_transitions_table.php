<?php

/**
 * Workflow流转规则表迁移
 *
 * 存储AI Agent工作流的流转规则
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * 创建featureai_workflow_transitions表
     * 用于定义工作流节点之间的流转规则和条件
     */
    public function up(): void
    {
        if (!Schema::hasTable('featureai_workflow_transitions')) {
            Schema::create('featureai_workflow_transitions', function (Blueprint $table) {
                $table->id()->comment('主键ID');

                // 外键字段
                $table->unsignedBigInteger('workflow_id')->comment('所属工作流ID');
                $table->unsignedBigInteger('from_node_id')->comment('源节点ID');
                $table->unsignedBigInteger('to_node_id')->comment('目标节点ID');

                // 规则字段
                $table->unsignedTinyInteger('condition_type')->default(1)->comment('条件类型:1无条件,2状态匹配,3自定义表达式');
                $table->json('condition_config')->nullable()->comment('条件配置JSON');
                $table->integer('priority')->default(1)->comment('优先级，数字越大优先级越高');

                $table->timestamps();

                // 索引
                $table->index('workflow_id', 'idx_workflow_id');
                $table->index('from_node_id', 'idx_from_node_id');
                $table->index('to_node_id', 'idx_to_node_id');
                $table->index(['workflow_id', 'from_node_id'], 'idx_workflow_from');
                $table->index('condition_type', 'idx_condition_type');

                // 外键约束
                $table->foreign('workflow_id', 'fk_transitions_workflow')
                    ->references('id')
                    ->on('featureai_workflow_definitions')
                    ->onDelete('cascade');

                $table->foreign('from_node_id', 'fk_transitions_from_node')
                    ->references('id')
                    ->on('featureai_workflow_nodes')
                    ->onDelete('cascade');

                $table->foreign('to_node_id', 'fk_transitions_to_node')
                    ->references('id')
                    ->on('featureai_workflow_nodes')
                    ->onDelete('cascade');

                $table->comment('FeatureAi模块-工作流流转规则表');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * 删除featureai_workflow_transitions表
     */
    public function down(): void
    {
        Schema::dropIfExists('featureai_workflow_transitions');
    }
};
