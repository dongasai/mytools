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
        Schema::create('feature_dbadmin_query_histories', function (Blueprint $table) {
            $table->id()->comment('主键ID');

            // 查询执行信息
            $table->unsignedInteger('user_id')->comment('执行用户ID');
            $table->string('connection_name', 50)->comment('数据库连接名');
            $table->text('sql_query')->comment('SQL 查询语句');
            $table->string('query_type', 20)->comment('查询类型: SELECT/INSERT/UPDATE/DELETE/DDL');
            $table->unsignedInteger('execution_time')->default(0)->comment('执行时间毫秒');
            $table->integer('row_count')->default(0)->comment('影响行数');
            $table->string('status', 20)->comment('执行状态: SUCCESS/FAILED');
            $table->text('error_message')->nullable()->comment('错误信息');
            $table->timestamp('executed_at')->comment('执行时间');

            // 时间戳
            $table->timestamps();

            // 索引
            $table->index('user_id', 'idx_query_histories_user_id');
            $table->index('connection_name', 'idx_query_histories_connection_name');
            $table->index('executed_at', 'idx_query_histories_executed_at');
            $table->index('query_type', 'idx_query_histories_query_type');

            $table->comment('SQL 查询历史表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_dbadmin_query_histories');
    }
};
