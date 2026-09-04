<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 创建动态Agent工具关联表.
 */
return new class extends Migration
{
    /**
     * 运行迁移.
     */
    public function up(): void
    {
        if (Schema::hasTable('featureai_dynamic_agent_tools')) {
            return;
        }

        Schema::create('featureai_dynamic_agent_tools', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id')->comment('Agent ID');
            $table->string('tool_class', 255)->comment('工具类名');
            $table->string('tool_name', 100)->nullable()->comment('工具名称');
            $table->json('tool_config')->nullable()->comment('工具配置参数');
            $table->integer('order_index')->default(0)->comment('排序');
            $table->boolean('is_enabled')->default(true)->comment('是否启用');

            $table->timestamps();

            // 外键约束
            $table->foreign('agent_id', 'fk_agent_tools_agent_id')
                ->references('id')
                ->on('featureai_dynamic_agents')
                ->onDelete('cascade');

            // 索引
            $table->index('agent_id', 'idx_agent_id');
            $table->index('tool_name', 'idx_tool_name');
            $table->index(['agent_id', 'is_enabled'], 'idx_agent_enabled');
        });

        // 添加表注释
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `featureai_dynamic_agent_tools` COMMENT='Agent工具关联表'");
        }
    }

    /**
     * 回滚迁移.
     */
    public function down(): void
    {
        Schema::dropIfExists('featureai_dynamic_agent_tools');
    }
};
