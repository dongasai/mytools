<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('feature_dbadmin_saved_queries', function (Blueprint $table) {
            $table->id()->comment('主键ID');

            // 查询基本信息
            $table->unsignedInteger('user_id')->comment('用户ID');
            $table->string('name', 200)->comment('查询名称');
            $table->text('description')->nullable()->comment('查询描述');
            $table->string('connection_name', 50)->comment('数据库连接名');
            $table->text('sql_query')->comment('SQL 查询语句');
            $table->json('tags')->nullable()->comment('标签');

            // 状态与统计
            $table->unsignedTinyInteger('is_public')->default(0)->comment('是否公开: 1是,0否');
            $table->unsignedInteger('use_count')->default(0)->comment('使用次数');
            $table->timestamp('last_used_at')->nullable()->comment('最后使用时间');

            // 时间戳
            $table->timestamps();
            $table->softDeletes()->comment('软删除时间');

            // 索引
            $table->index('user_id', 'idx_saved_queries_user_id');
            $table->index('is_public', 'idx_saved_queries_is_public');
            $table->index('connection_name', 'idx_saved_queries_connection_name');

            $table->comment('保存的查询表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_dbadmin_saved_queries');
    }
};
