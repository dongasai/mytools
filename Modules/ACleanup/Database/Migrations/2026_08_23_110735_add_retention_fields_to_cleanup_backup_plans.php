<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 为备份计划表添加保留策略字段
 *
 * 支持三种保留策略：
 * - 按数量保留：保留最近 N 个批次
 * - 按时间保留：保留最近 N 天的批次
 * - 混合策略：保留最近 N 个批次，但不超过 M 天
 */
return new class extends Migration
{
    /**
     * 执行迁移
     */
    public function up(): void
    {
        // 为 cleanup_backup_plans 表添加保留策略字段
        Schema::table('cleanup_backup_plans', function (Blueprint $table) {
            $table->unsignedInteger('retention_count')->default(0)
                ->comment('保留数量（0表示不限制）')
                ->after('last_batch_id');

            $table->unsignedTinyInteger('retention_strategy')->default(1)
                ->comment('保留策略:1按数量,2按时间,3混合')
                ->after('retention_count');

            $table->unsignedInteger('retention_days')->default(30)
                ->comment('保留天数（按时间策略时生效）')
                ->after('retention_strategy');

            $table->index('retention_strategy', 'idx_retention_strategy');
        });

        // 为 cleanup_backup_run_batches 表添加复合索引（优化清理查询）
        Schema::table('cleanup_backup_run_batches', function (Blueprint $table) {
            $table->index(['plan_id', 'batch_status', 'completed_at'], 'idx_plan_completed');
            $table->index(['plan_id', 'trigger_type', 'batch_status'], 'idx_plan_trigger');
        });
    }

    /**
     * 回滚迁移
     */
    public function down(): void
    {
        // 移除 cleanup_backup_plans 表的字段和索引
        Schema::table('cleanup_backup_plans', function (Blueprint $table) {
            $table->dropColumn('retention_count');
            $table->dropColumn('retention_strategy');
            $table->dropColumn('retention_days');
            $table->dropIndex('idx_retention_strategy');
        });

        // 移除 cleanup_backup_run_batches 表的索引
        Schema::table('cleanup_backup_run_batches', function (Blueprint $table) {
            $table->dropIndex('idx_plan_completed');
            $table->dropIndex('idx_plan_trigger');
        });
    }
};
