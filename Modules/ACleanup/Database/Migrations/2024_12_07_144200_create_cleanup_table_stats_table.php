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
        Schema::create('cleanup_table_stats', function (Blueprint $table) {
            $table->id()->comment('主键ID');
            $table->string('table_name', 100)->comment('表名');
            $table->unsignedBigInteger('record_count')->default(0)->comment('记录总数');
            $table->decimal('table_size_mb', 10, 2)->default(0.00)->comment('表大小(MB)');
            $table->decimal('index_size_mb', 10, 2)->default(0.00)->comment('索引大小(MB)');
            $table->decimal('data_free_mb', 10, 2)->default(0.00)->comment('碎片空间(MB)');
            $table->unsignedInteger('avg_row_length')->default(0)->comment('平均行长度');
            $table->unsignedBigInteger('auto_increment')->nullable()->comment('自增值');
            $table->timestamp('oldest_record_time')->nullable()->comment('最早记录时间');
            $table->timestamp('newest_record_time')->nullable()->comment('最新记录时间');
            $table->timestamp('scan_time')->useCurrent()->comment('扫描时间');
            $table->timestamps();

            // 索引
            $table->unique(['table_name', 'scan_time']);
            $table->index('table_name');
            $table->index('record_count');
            $table->index('table_size_mb');
            $table->index('scan_time');

            $table->comment('表统计信息表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cleanup_table_stats');
    }
};