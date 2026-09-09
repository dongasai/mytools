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
        Schema::table('feature_dbadmin_saved_queries', function (Blueprint $table) {
            // 添加连接ID字段（在 description 之后）
            $table->unsignedBigInteger('connection_id')->nullable()->after('description');
            $table->index('connection_id');

            // 添加数据库字段（在 connection_name 之后）
            $table->string('database', 100)->nullable()->after('connection_name');
            $table->index('database');

            // 添加模式字段（在 database 之后）
            $table->string('schema', 100)->nullable()->after('database');
            $table->index('schema');

            // 添加复合索引
            $table->index(['connection_id', 'database', 'schema']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feature_dbadmin_saved_queries', function (Blueprint $table) {
            // 删除索引
            $table->dropIndex(['connection_id', 'database', 'schema']);
            $table->dropIndex(['schema']);
            $table->dropIndex(['database']);
            $table->dropIndex(['connection_id']);

            // 删除字段
            $table->dropColumn(['connection_id', 'database', 'schema']);
        });
    }
};