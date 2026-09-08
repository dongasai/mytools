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
        Schema::create('cleanup_logs', function (Blueprint $table) {
            $table->id()->comment('主键ID');
            $table->unsignedBigInteger('task_id')->comment('任务ID');
            $table->string('table_name', 100)->comment('表名');
            $table->string('model_class')->nullable()->comment('Model类名');
            $table->unsignedTinyInteger('cleanup_type')->comment('清理类型:1清空表,2删除所有,3按时间删除,4按用户删除,5按条件删除');
            $table->unsignedBigInteger('before_count')->default(0)->comment('清理前记录数');
            $table->unsignedBigInteger('after_count')->default(0)->comment('清理后记录数');
            $table->unsignedBigInteger('deleted_records')->default(0)->comment('删除记录数');
            $table->decimal('execution_time', 8, 3)->default(0.000)->comment('执行时间(秒)');
            $table->json('conditions')->nullable()->comment('使用的清理条件');
            $table->text('error_message')->nullable()->comment('错误信息');
            $table->timestamp('created_at')->useCurrent()->comment('创建时间');

            // 索引
            $table->index('task_id');
            $table->index('table_name');
            $table->index('cleanup_type');
            $table->index('created_at');
            $table->index('model_class');

            $table->comment('清理日志表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cleanup_logs');
    }
};