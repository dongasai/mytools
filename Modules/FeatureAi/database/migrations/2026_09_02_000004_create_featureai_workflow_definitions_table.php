<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 创建Workflow定义表.
 *
 * 存储工作流的基础定义信息，包括名称、描述和启用状态
 */
return new class extends Migration
{
    /**
     * 运行迁移.
     */
    public function up(): void
    {
        if (Schema::hasTable('featureai_workflow_definitions')) {
            return;
        }

        Schema::create('featureai_workflow_definitions', function (Blueprint $table) {
            $table->id()->comment('主键ID');
            $table->string('name', 255)->comment('Workflow名称');
            $table->text('description')->nullable()->comment('描述');
            $table->boolean('is_active')->default(true)->comment('是否启用');

            $table->timestamps();

            // 索引
            $table->index('name', 'idx_name');
            $table->index('is_active', 'idx_is_active');
        });

        // 添加表注释
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `featureai_workflow_definitions` COMMENT='Workflow定义表'");
        }
    }

    /**
     * 回滚迁移.
     */
    public function down(): void
    {
        Schema::dropIfExists('featureai_workflow_definitions');
    }
};
