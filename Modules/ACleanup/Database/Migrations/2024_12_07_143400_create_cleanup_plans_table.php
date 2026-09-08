<?php

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
        Schema::create('cleanup_plans', function (Blueprint $table) {
            $table->id()->comment('主键ID');
            $table->string('plan_name', 100)->comment('计划名称');
            $table->unsignedTinyInteger('plan_type')->comment('计划类型:1全量清理,2模块清理,3分类清理,4自定义清理,5混合清理');
            $table->json('target_selection')->nullable()->comment('目标选择配置');
            $table->json('selected_tables')->nullable()->comment('选择的Model类列表，格式：["Modules\\System\\Models\\AdminActionlog"]');
            $table->json('global_conditions')->nullable()->comment('全局清理条件');
            $table->json('backup_config')->nullable()->comment('备份配置');
            $table->boolean('is_template')->default(0)->comment('是否为模板');
            $table->boolean('is_enabled')->default(1)->comment('是否启用');
            $table->text('description')->nullable()->comment('计划描述');
            $table->unsignedBigInteger('created_by')->nullable()->comment('创建者用户ID');
            $table->timestamps();

            // 索引
            $table->unique('plan_name');
            $table->index('plan_type');
            $table->index('is_template');
            $table->index('is_enabled');
            $table->index('created_by');

            $table->comment('清理计划表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cleanup_plans');
    }
};
