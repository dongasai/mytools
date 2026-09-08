<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 创建表备份进度记录表
 */
return new class extends Migration
{
    /**
     * 运行迁移
     */
    public function up(): void
    {
        Schema::create('cleanup_backup_run_tables', function (Blueprint $table) {
            $table->id()->comment('主键ID');
            $table->unsignedBigInteger('batch_id')->comment('批次ID');
            $table->string('table_name', 100)->comment('表名');
            $table->integer('total_records')->default(0)->comment('总记录数');
            $table->integer('processed_records')->default(0)->comment('已处理记录数');
            $table->decimal('progress_percent', 5, 2)->default(0)->comment('进度百分比');
            $table->boolean('is_large_table')->default(false)->comment('是否大表');
            $table->integer('split_count')->default(0)->comment('分批数量');
            $table->integer('completed_splits')->default(0)->comment('已完成分批数');
            $table->integer('table_status')->default(0)->comment('表状态:0等待,1执行中,2完成,3失败');
            $table->string('file_path', 500)->nullable()->comment('备份文件路径');
            $table->string('file_name', 200)->nullable()->comment('备份文件名');
            $table->integer('file_size')->default(0)->comment('文件大小(字节)');
            $table->string('file_hash', 64)->nullable()->comment('文件SHA256哈希');
            $table->text('error_message')->nullable()->comment('错误信息');
            $table->timestamp('started_at')->nullable()->comment('开始时间');
            $table->timestamp('completed_at')->nullable()->comment('完成时间');
            $table->timestamps();

            $table->index('batch_id');
            $table->index('table_name');
            $table->index('table_status');
        });
    }

    /**
     * 回滚迁移
     */
    public function down(): void
    {
        Schema::dropIfExists('cleanup_backup_run_tables');
    }
};