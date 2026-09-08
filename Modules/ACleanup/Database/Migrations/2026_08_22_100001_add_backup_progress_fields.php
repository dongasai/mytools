<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 添加备份进度跟踪字段
 *
 * 为 cleanup_backups 表添加批次进度相关字段
 */
return new class extends Migration
{
    /**
     * 运行迁移
     */
    public function up(): void
    {
        Schema::table('cleanup_backups', function (Blueprint $table) {
            // 批次进度字段
            $table->unsignedInteger('total_tables')->default(0)->comment('总表数')->after('records_count');
            $table->unsignedInteger('processed_tables')->default(0)->comment('已处理表数')->after('total_tables');
            $table->unsignedInteger('failed_tables')->default(0)->comment('失败表数')->after('processed_tables');
            $table->decimal('progress_percent', 5, 2)->default(0.00)->comment('批次进度百分比')->after('failed_tables');

            // 性能统计字段
            $table->unsignedInteger('avg_speed')->default(0)->comment('平均速度(条/秒)')->after('progress_percent');
            $table->unsignedInteger('estimated_remaining')->default(0)->comment('预计剩余秒数')->after('avg_speed');
            $table->string('current_table', 100)->nullable()->comment('当前处理的表')->after('estimated_remaining');

            // 批次信息字段
            $table->json('tables_json')->nullable()->comment('表列表信息')->after('current_table');
            $table->json('processed_tables_json')->nullable()->comment('已处理表列表')->after('tables_json');
        });
    }

    /**
     * 回滚迁移
     */
    public function down(): void
    {
        Schema::table('cleanup_backups', function (Blueprint $table) {
            $table->dropColumn([
                'total_tables',
                'processed_tables',
                'failed_tables',
                'progress_percent',
                'avg_speed',
                'estimated_remaining',
                'current_table',
                'tables_json',
                'processed_tables_json',
            ]);
        });
    }
};