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
        Schema::create('cleanup_backups', function (Blueprint $table) {
            $table->id()->comment('主键ID');
            $table->unsignedBigInteger('plan_id')->comment('关联的清理计划ID');
            $table->unsignedBigInteger('task_id')->nullable()->comment('关联的清理任务ID(如果是任务触发的备份)');
            $table->string('backup_name', 100)->comment('备份名称');
            $table->unsignedTinyInteger('backup_type')->comment('备份类型:1SQL,2JSON,3CSV');
            $table->unsignedTinyInteger('compression_type')->default(1)->comment('压缩类型:1none,2gzip,3zip');
            $table->string('backup_path', 500)->comment('备份文件路径');
            $table->unsignedBigInteger('backup_size')->default(0)->comment('备份文件大小(字节)');
            $table->unsignedBigInteger('original_size')->default(0)->comment('原始数据大小(字节)');
            $table->unsignedInteger('tables_count')->default(0)->comment('备份表数量');
            $table->unsignedBigInteger('records_count')->default(0)->comment('备份记录数量');
            $table->unsignedTinyInteger('backup_status')->default(1)->comment('备份状态:1进行中,2已完成,3已失败');
            $table->string('backup_hash', 64)->nullable()->comment('备份文件MD5哈希');
            $table->json('backup_config')->nullable()->comment('备份配置信息');
            $table->timestamp('started_at')->nullable()->comment('备份开始时间');
            $table->timestamp('completed_at')->nullable()->comment('备份完成时间');
            $table->timestamp('expires_at')->nullable()->comment('备份过期时间');
            $table->text('error_message')->nullable()->comment('错误信息');
            $table->unsignedBigInteger('created_by')->nullable()->comment('创建者用户ID');
            $table->timestamps();

            // 索引
            $table->index('plan_id');
            $table->index('task_id');
            $table->index('backup_status');
            $table->index('expires_at');
            $table->index('created_at');

            $table->comment('备份记录表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cleanup_backups');
    }
};