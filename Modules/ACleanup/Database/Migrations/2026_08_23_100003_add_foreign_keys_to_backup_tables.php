<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 添加外键约束
 *
 * 为表备份和分批记录添加外键约束，确保数据完整性
 */
return new class extends Migration
{
    /**
     * 运行迁移
     */
    public function up(): void
    {
        // cleanup_backup_run_tables 添加外键约束
        Schema::table('cleanup_backup_run_tables', function (Blueprint $table) {
            $table->foreign('batch_id')
                ->references('id')
                ->on('cleanup_backup_run_batches')
                ->onDelete('cascade');
        });

        // cleanup_backup_run_table_splits 添加外键约束
        Schema::table('cleanup_backup_run_table_splits', function (Blueprint $table) {
            $table->foreign('table_id')
                ->references('id')
                ->on('cleanup_backup_run_tables')
                ->onDelete('cascade');

            $table->foreign('batch_id')
                ->references('id')
                ->on('cleanup_backup_run_batches')
                ->onDelete('cascade');
        });
    }

    /**
     * 回滚迁移
     */
    public function down(): void
    {
        // cleanup_backup_run_table_splits 删除外键
        Schema::table('cleanup_backup_run_table_splits', function (Blueprint $table) {
            $table->dropForeign(['table_id']);
            $table->dropForeign(['batch_id']);
        });

        // cleanup_backup_run_tables 删除外键
        Schema::table('cleanup_backup_run_tables', function (Blueprint $table) {
            $table->dropForeign(['batch_id']);
        });
    }
};
