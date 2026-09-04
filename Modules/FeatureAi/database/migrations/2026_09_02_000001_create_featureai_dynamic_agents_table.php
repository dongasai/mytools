<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 创建动态Agent定义表.
 */
return new class extends Migration
{
    /**
     * 运行迁移.
     */
    public function up(): void
    {
        if (Schema::hasTable('featureai_dynamic_agents')) {
            return;
        }

        Schema::create('featureai_dynamic_agents', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->comment('Agent名称');
            $table->string('slug', 100)->unique()->comment('URL友好标识');
            $table->text('description')->nullable()->comment('描述');
            $table->boolean('is_active')->default(true)->comment('是否启用');

            // Provider 配置
            $table->string('provider_class', 255)->comment('Provider类名');
            $table->json('provider_config')->nullable()->comment('Provider配置（api_key, model等）');

            // Instructions
            $table->text('instructions')->comment('系统提示词');

            // 行为配置
            $table->integer('tool_max_runs')->default(10)->comment('工具最大调用次数');
            $table->boolean('parallel_tool_calls')->default(false)->comment('是否并行执行工具');

            // 持久化
            $table->enum('persistence_driver', ['database', 'file', 'memory'])->default('database')->comment('持久化驱动');

            $table->timestamps();

            // 索引
            $table->index('slug', 'idx_slug');
            $table->index('is_active', 'idx_is_active');
        });

        // 添加表注释
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `featureai_dynamic_agents` COMMENT='动态Agent定义表'");
        }
    }

    /**
     * 回滚迁移.
     */
    public function down(): void
    {
        Schema::dropIfExists('featureai_dynamic_agents');
    }
};
