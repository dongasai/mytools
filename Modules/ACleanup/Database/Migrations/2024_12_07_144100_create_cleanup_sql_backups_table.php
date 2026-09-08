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
        Schema::create('cleanup_sql_backups', function (Blueprint $table) {
            $table->id()->comment('主键ID');
            $table->unsignedBigInteger('backup_id')->comment('备份记录ID');
            $table->string('table_name', 100)->comment('表名');
            $table->longText('sql_content')->comment('INSERT语句内容');
            $table->unsignedBigInteger('records_count')->default(0)->comment('记录数量');
            $table->unsignedBigInteger('content_size')->default(0)->comment('内容大小(字节)');
            $table->string('content_hash', 64)->nullable()->comment('内容SHA256哈希');
            $table->json('backup_conditions')->nullable()->comment('备份条件');
            $table->timestamp('created_at')->useCurrent()->comment('创建时间');

            // 索引
            $table->index('backup_id');
            $table->index('table_name');
            $table->index('records_count');
            $table->index('content_size');

            $table->comment('SQL备份表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cleanup_sql_backups');
    }
};