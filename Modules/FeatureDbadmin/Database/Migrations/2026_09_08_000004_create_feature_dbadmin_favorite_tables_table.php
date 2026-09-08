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
        Schema::create('feature_dbadmin_favorite_tables', function (Blueprint $table) {
            $table->id()->comment('主键ID');

            // 收藏信息
            $table->unsignedInteger('user_id')->comment('用户ID');
            $table->string('connection_name', 50)->comment('数据库连接名');
            $table->string('table_name', 100)->comment('表名');
            $table->string('schema_name', 100)->nullable()->comment('模式名');
            $table->string('alias', 100)->nullable()->comment('别名');
            $table->text('notes')->nullable()->comment('备注');

            // 时间戳
            $table->timestamps();

            // 唯一索引
            $table->unique(['user_id', 'connection_name', 'table_name', 'schema_name'], 'idx_unique_favorite');

            $table->comment('收藏的表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_dbadmin_favorite_tables');
    }
};
