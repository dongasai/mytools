<?php

/**
 * 创建动态Agent表
 *
 * 用于存储动态配置的Agent实例，支持灵活的AI Agent管理
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
        // 创建 featureai_dynamic_agents 表
        if (!Schema::hasTable('featureai_dynamic_agents')) {
            Schema::create('featureai_dynamic_agents', function (Blueprint $table) {
                // 主键
                $table->id();

                // Agent基本信息
                $table->string('name')->comment('Agent名称');
                $table->string('slug')->unique()->comment('URL友好标识，唯一');
                $table->text('description')->nullable()->comment('Agent描述');

                // Agent状态
                $table->boolean('is_active')->default(true)->comment('是否启用');

                // Provider配置
                $table->string('provider_class')->comment('Provider类名');
                $table->json('provider_config')->comment('Provider配置');

                // Agent行为配置
                $table->text('instructions')->comment('Agent指令');
                $table->integer('tool_max_runs')->default(5)->comment('工具最大运行次数');
                $table->boolean('parallel_tool_calls')->default(false)->comment('是否并行调用工具');

                // 持久化配置
                $table->string('persistence_driver')->default('memory')->comment('持久化驱动');

                // 时间戳
                $table->timestamps();

                // 索引
                $table->index('slug');
                $table->index('is_active');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('featureai_dynamic_agents');
    }
};