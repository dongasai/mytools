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
        Schema::create('feature_dbadmin_table_snapshots', function (Blueprint $table) {
            $table->id()->comment('主键ID');

            // 表基本信息
            $table->string('connection_name', 50)->comment('数据库连接名');
            $table->string('table_name', 100)->comment('表名');
            $table->string('schema_name', 100)->nullable()->comment('模式名');

            // 表结构信息
            $table->json('table_structure')->comment('表结构JSON');
            $table->unsignedInteger('column_count')->default(0)->comment('列数');
            $table->unsignedInteger('index_count')->default(0)->comment('索引数');
            $table->unsignedInteger('foreign_key_count')->default(0)->comment('外键数');
            $table->unsignedBigInteger('row_count')->default(0)->comment('行数');
            $table->string('table_size', 50)->nullable()->comment('表大小');

            // 快照时间
            $table->timestamp('snapshot_at')->comment('快照时间');

            // 时间戳
            $table->timestamps();

            // 索引
            $table->index('connection_name', 'idx_connection_name');
            $table->index('table_name', 'idx_table_name');
            $table->index('snapshot_at', 'idx_snapshot_at');

            $table->comment('表结构快照表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_dbadmin_table_snapshots');
    }
};
