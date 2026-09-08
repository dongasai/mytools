<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 添加缺失字段：table_size、avg_speed、estimated_remaining
 *
 * 用于记录备份表大小和进度监控指标
 */
return new class extends Migration
{
    /**
     * 运行迁移
     */
    public function up(): void
    {
        // cleanup_backup_run_tables 表添加 table_size 字段
        Schema::table('cleanup_backup_run_tables', function (Blueprint $table) {
            $table->unsignedBigInteger('table_size')->default(0)->comment('表大小(字节)')->after('is_large_table');
        });

        // cleanup_backup_run_batches 表添加 avg_speed 和 estimated_remaining 字段
        Schema::table('cleanup_backup_run_batches', function (Blueprint $table) {
            $table->unsignedInteger('avg_speed')->default(0)->comment('平均速度(条/秒)')->after('progress_percent');
            $table->unsignedInteger('estimated_remaining')->default(0)->comment('预计剩余秒数')->after('avg_speed');
        });
    }

    /**
     * 回滚迁移
     */
    public function down(): void
    {
        Schema::table('cleanup_backup_run_tables', function (Blueprint $table) {
            $table->dropColumn('table_size');
        });

        Schema::table('cleanup_backup_run_batches', function (Blueprint $table) {
            $table->dropColumn('avg_speed');
            $table->dropColumn('estimated_remaining');
        });
    }
};
