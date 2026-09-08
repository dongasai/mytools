<?php

/**
 * 修改 cleanup_backups 表支持独立备份
 *
 * 变更内容：
 * 1. plan_id 改为 nullable（支持不关联清理计划的独立备份）
 * 2. 新增 source_type 字段区分备份来源：
 *    - 1 = CLEANUP_PLAN（清理计划触发的备份）
 *    - 2 = BACKUP_PLAN（独立备份计划触发的备份）
 *    - 3 = MANUAL（手动触发的备份）
 *
 * @see Modules\AClean\Database\Migrations\2024_12_07_143600_create_cleanup_backups_table.php 原始建表迁移
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 执行迁移：修改 cleanup_backups 表结构
     */
    public function up(): void
    {
        // plan_id 改 nullable：允许独立备份不关联清理计划
        if (Schema::hasTable('cleanup_backups') && Schema::hasColumn('cleanup_backups', 'plan_id')) {
            Schema::table('cleanup_backups', function (Blueprint $table) {
                $table->unsignedBigInteger('plan_id')->nullable()->change();
            });
        }

        // 新增 source_type：标识备份来源类型
        if (Schema::hasTable('cleanup_backups') && !Schema::hasColumn('cleanup_backups', 'source_type')) {
            Schema::table('cleanup_backups', function (Blueprint $table) {
                $table->unsignedTinyInteger('source_type')
                    ->nullable()
                    ->after('plan_id')
                    ->comment('备份来源类型:1清理计划(CLEANUP_PLAN),2独立备份计划(BACKUP_PLAN),3手动备份(MANUAL)');

                $table->index('source_type', 'idx_cleanup_backups_source_type');
            });
        }
    }

    /**
     * 回滚迁移：恢复 cleanup_backups 表原始结构
     */
    public function down(): void
    {
        if (Schema::hasTable('cleanup_backups')) {
            Schema::table('cleanup_backups', function (Blueprint $table) {
                // 删除 source_type 索引和字段
                if (Schema::hasColumn('cleanup_backups', 'source_type')) {
                    $table->dropIndex('idx_cleanup_backups_source_type');
                    $table->dropColumn('source_type');
                }

                // 恢复 plan_id 为 NOT NULL
                if (Schema::hasColumn('cleanup_backups', 'plan_id')) {
                    $table->unsignedBigInteger('plan_id')->nullable(false)->change();
                }
            });
        }
    }
};
