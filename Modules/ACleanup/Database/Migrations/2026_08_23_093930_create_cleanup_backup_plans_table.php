<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 创建备份计划表
 *
 * 备份计划关联备份配置，支持定期执行
 * - 定义调度策略（每天、每周、每月）
 * - 记录执行历史
 * - 支持启用/禁用
 */
return new class extends Migration
{
    /**
     * 执行迁移
     */
    public function up(): void
    {
        Schema::create('cleanup_backup_plans', function (Blueprint $table) {
            $table->id()->comment('主键ID');
            $table->string('plan_name', 100)->unique()->comment('计划名称（唯一）');
            $table->unsignedBigInteger('config_id')->comment('关联的备份配置ID');

            // 调度配置
            $table->string('schedule_type', 20)->default('daily')->comment('调度类型:daily,weekly,monthly,custom');
            $table->json('schedule_config')->nullable()->comment('调度配置（cron表达式或具体时间）');

            // 执行时间
            $table->time('run_time')->default('00:00:00')->comment('执行时间（时分秒）');
            $table->unsignedTinyInteger('run_day')->default(1)->comment('执行日期（周几或几号，1-7或1-31）');

            // 执行状态
            $table->boolean('is_enabled')->default(true)->comment('是否启用');
            $table->timestamp('next_run_at')->nullable()->comment('下次执行时间');
            $table->timestamp('last_run_at')->nullable()->comment('上次执行时间');
            $table->unsignedBigInteger('last_batch_id')->nullable()->comment('上次执行的批次ID');

            $table->text('description')->nullable()->comment('计划描述');
            $table->unsignedBigInteger('created_by')->nullable()->comment('创建人ID');
            $table->timestamps();

            // 索引
            $table->index('config_id', 'idx_config_id');
            $table->index('is_enabled', 'idx_is_enabled');
            $table->index('next_run_at', 'idx_next_run_at');
            $table->index('last_run_at', 'idx_last_run_at');

            // 外键
            $table->foreign('config_id')
                ->references('id')
                ->on('cleanup_backup_configs')
                ->onDelete('cascade');

            $table->comment('备份计划表（定期执行配置）');
        });
    }

    /**
     * 回滚迁移
     */
    public function down(): void
    {
        Schema::dropIfExists('cleanup_backup_plans');
    }
};
