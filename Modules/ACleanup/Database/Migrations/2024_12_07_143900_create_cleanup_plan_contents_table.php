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
        Schema::create('cleanup_plan_contents', function (Blueprint $table) {
            $table->id()->comment('主键ID');
            $table->unsignedBigInteger('plan_id')->comment('计划ID');
            $table->string('table_name', 100)->comment('表名');
            $table->string('model_class')->nullable()->comment('Model类名');
            $table->unsignedTinyInteger('cleanup_type')->comment('清理类型:1清空表,2删除所有,3按时间删除,4按用户删除,5按条件删除');
            $table->json('conditions')->nullable()->comment('清理条件JSON配置');
            $table->unsignedInteger('priority')->default(100)->comment('清理优先级');
            $table->unsignedInteger('batch_size')->default(1000)->comment('批处理大小');
            $table->boolean('is_enabled')->default(1)->comment('是否启用');
            $table->boolean('backup_enabled')->default(1)->comment('是否启用备份');
            $table->text('notes')->nullable()->comment('备注说明');
            $table->timestamps();

            // 索引
            $table->unique(['plan_id', 'table_name']);
            $table->index('plan_id');
            $table->index('table_name');
            $table->index('priority');
            $table->index('model_class');

            $table->comment('计划内容表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cleanup_plan_contents');
    }
};