<?php

/**
 * Workflow定义表迁移
 *
 * 存储AI Agent工作流的定义信息
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * 创建featureai_workflow_definitions表
     * 用于存储工作流定义，包括工作流名称、描述和启用状态
     */
    public function up(): void
    {
        if (!Schema::hasTable('featureai_workflow_definitions')) {
            Schema::create('featureai_workflow_definitions', function (Blueprint $table) {
                $table->id()->comment('主键ID');

                // 基础字段
                $table->string('name', 255)->comment('工作流名称');
                $table->text('description')->nullable()->comment('工作流描述');
                $table->boolean('is_active')->default(true)->comment('是否启用:1启用,0禁用');

                $table->timestamps();

                // 索引
                $table->index('name', 'idx_name');

                $table->comment('FeatureAi模块-工作流定义表');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * 删除featureai_workflow_definitions表
     */
    public function down(): void
    {
        Schema::dropIfExists('featureai_workflow_definitions');
    }
};
