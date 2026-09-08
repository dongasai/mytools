<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 为备份批次表添加触发类型字段
 *
 * 区分手动触发和定时触发（自动）两种执行方式
 */
return new class extends Migration
{
    /**
     * 执行迁移
     */
    public function up(): void
    {
        Schema::table('cleanup_backup_run_batches', function (Blueprint $table) {
            // 触发类型：manual-手动，scheduled-定时
            $table->string('trigger_type', 20)->default('manual')
                ->comment('触发类型:manual手动,scheduled定时')
                ->after('batch_name');

            // 触发人ID（手动时记录操作用户）
            $table->unsignedBigInteger('triggered_by')->nullable()
                ->comment('触发人ID（手动时记录）')
                ->after('trigger_type');

            // 索引
            $table->index('trigger_type', 'idx_trigger_type');
        });
    }

    /**
     * 回滚迁移
     */
    public function down(): void
    {
        Schema::table('cleanup_backup_run_batches', function (Blueprint $table) {
            $table->dropColumn('trigger_type');
            $table->dropColumn('triggered_by');
            $table->dropIndex('idx_trigger_type');
        });
    }
};
