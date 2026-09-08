<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 创建备份执行日志表
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cleanup_backup_run_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('batch_id')->comment('批次ID');
            $table->unsignedBigInteger('table_id')->nullable()->comment('表备份ID');
            $table->unsignedBigInteger('split_id')->nullable()->comment('分片ID');
            $table->string('level', 20)->default('info')->comment('日志级别: info, warning, error');
            $table->string('message')->comment('日志消息');
            $table->json('context')->nullable()->comment('上下文数据');
            $table->timestamps();

            $table->index('batch_id');
            $table->index(['batch_id', 'created_at']);

            $table->comment('备份执行日志表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cleanup_backup_run_logs');
    }
};