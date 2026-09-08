<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 更新分片策略：将offset/limit改为主键范围start_id/end_id
 *
 * 这是为了解决大表备份时offset性能问题
 */
return new class extends Migration
{
    /**
     * 运行迁移
     */
    public function up(): void
    {
        Schema::table('cleanup_backup_run_table_splits', function (Blueprint $table) {
            // 添加主键范围字段
            $table->unsignedBigInteger('start_id')->default(0)->comment('起始主键ID（包含）')->after('split_index');
            $table->unsignedBigInteger('end_id')->default(0)->comment('结束主键ID（不包含）')->after('start_id');

            // 删除旧的offset/limit字段
            $table->dropColumn('offset');
            $table->dropColumn('limit');

            // 添加主键范围索引
            $table->index(['table_name', 'start_id', 'end_id'], 'idx_split_range');
        });
    }

    /**
     * 回滚迁移
     */
    public function down(): void
    {
        Schema::table('cleanup_backup_run_table_splits', function (Blueprint $table) {
            // 添加回旧字段
            $table->integer('offset')->default(0)->comment('起始偏移量')->after('split_index');
            $table->integer('limit')->default(5000)->comment('每批数量')->after('offset');

            // 删除新字段
            $table->dropColumn('start_id');
            $table->dropColumn('end_id');

            // 删除新索引
            $table->dropIndex('idx_split_range');
        });
    }
};
