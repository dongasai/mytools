<?php

/**
 * 创建 cleanup_backup_plans 表：独立备份计划
 *
 * 独立备份计划不依赖清理计划，允许用户单独配置备份策略：
 * - 选择目标表/Model
 * - 配置备份类型（SQL/JSON/CSV）
 * - 配置压缩方式和保留天数
 * - 独立启用/禁用控制
 *
 * @see Modules\AClean\Database\Migrations\2026_08_02_100000_modify_cleanup_backups_for_standalone.php 关联的 cleanup_backups 表变更
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 执行迁移：创建 cleanup_backup_plans 表
     */
    public function up(): void
    {
        if (!Schema::hasTable('cleanup_backup_plans')) {
            Schema::create('cleanup_backup_plans', function (Blueprint $table) {
                $table->id()->comment('主键ID');
                $table->string('plan_name', 100)->unique()->comment('备份计划名称（唯一）');
                $table->json('selected_tables')->comment('目标表/Model类列表，JSON格式');
                $table->unsignedTinyInteger('backup_type')->comment('备份类型:1DATABASE,2SQL,3JSON,4CSV（复用BACKUP_TYPE枚举值）');
                $table->unsignedTinyInteger('compression_type')->default(1)->comment('压缩类型:1none,2gzip,3zip');
                $table->unsignedSmallInteger('retention_days')->default(30)->comment('备份保留天数，0表示永久保留');
                $table->boolean('is_enabled')->default(true)->comment('是否启用:1启用,0禁用');
                $table->text('description')->nullable()->comment('备份计划描述');
                $table->unsignedBigInteger('created_by')->nullable()->comment('创建人用户ID');
                $table->timestamps();

                // 索引
                $table->index('is_enabled', 'idx_cleanup_backup_plans_is_enabled');
                $table->index('created_by', 'idx_cleanup_backup_plans_created_by');

                $table->comment('独立备份计划表');
            });
        }
    }

    /**
     * 回滚迁移：删除 cleanup_backup_plans 表
     */
    public function down(): void
    {
        Schema::dropIfExists('cleanup_backup_plans');
    }
};
