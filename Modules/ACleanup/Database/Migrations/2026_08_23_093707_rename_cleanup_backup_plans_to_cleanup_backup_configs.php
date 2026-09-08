<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * 重命名表：cleanup_backup_plans → cleanup_backup_configs
 *
 * 统一命名规范，与模块名 AClean 保持一致
 */
return new class extends Migration
{
    /**
     * 执行迁移：重命名表
     */
    public function up(): void
    {
        if (Schema::hasTable('cleanup_backup_plans')) {
            Schema::rename('cleanup_backup_plans', 'cleanup_backup_configs');
        }
    }

    /**
     * 回滚迁移：恢复原表名
     */
    public function down(): void
    {
        if (Schema::hasTable('cleanup_backup_configs')) {
            Schema::rename('cleanup_backup_configs', 'cleanup_backup_plans');
        }
    }
};
