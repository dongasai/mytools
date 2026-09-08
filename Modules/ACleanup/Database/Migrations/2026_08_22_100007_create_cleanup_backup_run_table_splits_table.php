<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 创建大表分批进度记录表
 */
return new class extends Migration
{
    /**
     * 运行迁移
     */
    public function up(): void
    {
        Schema::create('cleanup_backup_run_table_splits', function (Blueprint $table) {
            $table->id()->comment('主键ID');
            $table->unsignedBigInteger('table_id')->comment('表备份ID');
            $table->unsignedBigInteger('batch_id')->comment('批次ID（冗余，便于查询）');
            $table->string('table_name', 100)->comment('表名（冗余）');
            $table->integer('split_index')->default(0)->comment('分批序号');
            $table->integer('offset')->default(0)->comment('起始偏移量');
            $table->integer('limit')->default(5000)->comment('每批数量');
            $table->integer('records_count')->default(0)->comment('实际记录数');
            $table->integer('split_status')->default(0)->comment('分批状态:0等待,1执行中,2完成,3失败');
            $table->text('error_message')->nullable()->comment('错误信息');
            $table->timestamp('started_at')->nullable()->comment('开始时间');
            $table->timestamp('completed_at')->nullable()->comment('完成时间');
            $table->timestamps();

            $table->index('table_id');
            $table->index('batch_id');
            $table->index(['table_name', 'split_index']);
            $table->index('split_status');
        });
    }

    /**
     * 回滚迁移
     */
    public function down(): void
    {
        Schema::dropIfExists('cleanup_backup_run_table_splits');
    }
};