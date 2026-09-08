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
        Schema::create('cleanup_tasks', function (Blueprint $table) {
            $table->id()->comment('主键ID');
            $table->string('task_name', 100)->comment('任务名称');
            $table->unsignedBigInteger('plan_id')->comment('关联的清理计划ID');
            $table->unsignedBigInteger('backup_id')->nullable()->comment('关联的备份ID');
            $table->unsignedTinyInteger('status')->default(1)->comment('任务状态:1待执行,2备份中,3执行中,4已完成,5已失败,6已取消,7已暂停');
            $table->decimal('progress', 5, 2)->default(0.00)->comment('执行进度百分比');
            $table->string('current_step', 50)->nullable()->comment('当前执行步骤');
            $table->unsignedInteger('total_tables')->default(0)->comment('总表数');
            $table->unsignedInteger('processed_tables')->default(0)->comment('已处理表数');
            $table->unsignedBigInteger('total_records')->default(0)->comment('总记录数');
            $table->unsignedBigInteger('deleted_records')->default(0)->comment('已删除记录数');
            $table->unsignedBigInteger('backup_size')->default(0)->comment('备份文件大小(字节)');
            $table->decimal('execution_time', 10, 3)->default(0.000)->comment('执行时间(秒)');
            $table->decimal('backup_time', 10, 3)->default(0.000)->comment('备份时间(秒)');
            $table->timestamp('started_at')->nullable()->comment('开始时间');
            $table->timestamp('backup_completed_at')->nullable()->comment('备份完成时间');
            $table->timestamp('completed_at')->nullable()->comment('完成时间');
            $table->text('error_message')->nullable()->comment('错误信息');
            $table->unsignedBigInteger('created_by')->nullable()->comment('创建者用户ID');
            $table->timestamps();

            // 索引
            $table->index('plan_id');
            $table->index('backup_id');
            $table->index('status');
            $table->index('created_by');
            $table->index('created_at');

            $table->comment('清理任务表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cleanup_tasks');
    }
};