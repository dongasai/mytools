<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 创建Workflow流转规则表.
 *
 * 存储工作流节点之间的流转规则，包括条件判断和优先级
 */
return new class extends Migration
{
    /**
     * 运行迁移.
     */
    public function up(): void
    {
        if (Schema::hasTable('featureai_workflow_transitions')) {
            return;
        }

        Schema::create('featureai_workflow_transitions', function (Blueprint $table) {
            $table->id()->comment('主键ID');

            // 外键字段
            $table->unsignedBigInteger('workflow_id')->comment('关联Workflow定义ID');
            $table->unsignedBigInteger('from_node_id')->comment('源节点ID');
            $table->unsignedBigInteger('to_node_id')->comment('目标节点ID');

            // 流转规则
            $table->enum('condition_type', ['always', 'state_match', 'expression'])->comment('条件类型:always总是,state_match状态匹配,expression表达式');
            $table->json('condition_config')->nullable()->comment('条件配置');
            $table->integer('priority')->default(1)->comment('优先级，数字越大优先级越高');

            $table->timestamps();

            // 索引
            $table->index('workflow_id', 'idx_workflow_id');
            $table->index('from_node_id', 'idx_from_node_id');
            $table->index('to_node_id', 'idx_to_node_id');
            $table->index('condition_type', 'idx_condition_type');
            $table->index(['workflow_id', 'from_node_id', 'priority'], 'idx_workflow_from_priority');

            // 外键约束
            $table->foreign('workflow_id')
                ->references('id')
                ->on('featureai_workflow_definitions')
                ->onDelete('cascade');

            $table->foreign('from_node_id')
                ->references('id')
                ->on('featureai_workflow_nodes')
                ->onDelete('cascade');

            $table->foreign('to_node_id')
                ->references('id')
                ->on('featureai_workflow_nodes')
                ->onDelete('cascade');
        });

        // 添加表注释
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `featureai_workflow_transitions` COMMENT='Workflow流转规则表'");
        }
    }

    /**
     * 回滚迁移.
     */
    public function down(): void
    {
        Schema::dropIfExists('featureai_workflow_transitions');
    }
};
