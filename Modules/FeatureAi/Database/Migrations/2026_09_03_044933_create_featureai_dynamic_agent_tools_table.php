<?php

/**
 * 创建动态Agent工具关联表
 *
 * 用于存储Agent与工具的关联关系，支持灵活的工具配置
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
        // 创建 featureai_dynamic_agent_tools 表
        if (!Schema::hasTable('featureai_dynamic_agent_tools')) {
            Schema::create('featureai_dynamic_agent_tools', function (Blueprint $table) {
                // 主键
                $table->id();

                // 外键关联
                $table->foreignId('agent_id')
                    ->constrained('featureai_dynamic_agents')
                    ->onDelete('cascade')
                    ->comment('关联的Agent ID');

                // 工具配置
                $table->string('tool_class')->comment('工具类名');
                $table->string('tool_name')->comment('工具名称');
                $table->text('tool_description')->nullable()->comment('工具描述');
                $table->json('tool_config')->comment('工具配置');

                // 工具状态和顺序
                $table->integer('order_index')->default(0)->comment('执行顺序');
                $table->boolean('is_enabled')->default(true)->comment('是否启用');

                // 时间戳
                $table->timestamps();

                // 索引
                $table->index('agent_id');
                $table->index('order_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('featureai_dynamic_agent_tools');
    }
};