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
        Schema::create('cleanup_backup_files', function (Blueprint $table) {
            $table->id()->comment('主键ID');
            $table->unsignedBigInteger('backup_id')->comment('备份记录ID');
            $table->string('table_name', 100)->comment('表名');
            $table->string('file_name')->comment('文件名');
            $table->string('file_path', 500)->comment('文件路径');
            $table->unsignedBigInteger('file_size')->default(0)->comment('文件大小(字节)');
            $table->string('file_hash', 64)->nullable()->comment('文件SHA256哈希');
            $table->unsignedTinyInteger('backup_type')->comment('备份类型:1SQL,2JSON,3CSV');
            $table->unsignedTinyInteger('compression_type')->default(1)->comment('压缩类型:1none,2gzip,3zip');
            $table->timestamp('created_at')->useCurrent()->comment('创建时间');

            // 索引
            $table->index('backup_id');
            $table->index('table_name');

            $table->comment('备份文件表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cleanup_backup_files');
    }
};