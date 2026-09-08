<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 创建批次进度记录表
 */
return new class extends Migration
{
    /**
     * 运行迁移
     */
    public function up(): void
    {
        Schema::create('cleanup_backup_run_batches', function (Blueprint $table) {
            $table->id()->comment('主键ID');
            $table->unsignedBigInteger('plan_id')->comment('备份计划ID');
            $table->string('batch_name', 200)->comment('批次名称');
            $table->integer('total_tables')->default(0)->comment('总表数');
            $table->integer('processed_tables')->default(0)->comment('已处理表数');
            $table->integer('failed_tables')->default(0)->comment('失败表数');
            $table->decimal('progress_percent', 5, 2)->default(0)->comment('进度百分比');
            $table->string('current_table', 100)->nullable()->comment('当前处理的表');
            $table->integer('backup_type')->default(1)->comment('备份类型:1SQL,2JSON,3CSV');
            $table->integer('compression_type')->default(1)->comment('压缩类型:1none,2gzip,3zip');
            $table->string('backup_path', 500)->comment('备份路径');
            $table->integer('batch_status')->default(0)->comment('批次状态:0等待,1执行中,2完成,3失败,4取消');
            $table->text('error_message')->nullable()->comment('错误信息');
            $table->timestamp('started_at')->nullable()->comment('开始时间');
            $table->timestamp('completed_at')->nullable()->comment('完成时间');
            $table->integer('created_by')->default(0)->comment('创建人ID');
            $table->timestamps();

            $table->index('plan_id');
            $table->index('batch_status');
        });
    }

    /**
     * 回滚迁移
     */
    public function down(): void
    {
        Schema::dropIfExists('cleanup_backup_run_batches');
    }
};