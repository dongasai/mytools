<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 添加备份文件进度字段
 */
return new class extends Migration
{
    /**
     * 运行迁移
     */
    public function up(): void
    {
        Schema::table('cleanup_backup_files', function (Blueprint $table) {
            $table->unsignedBigInteger('records_count')->default(0)->comment('记录数')->after('file_size');
            $table->boolean('is_completed')->default(false)->comment('是否完成')->after('records_count');
        });
    }

    /**
     * 回滚迁移
     */
    public function down(): void
    {
        Schema::table('cleanup_backup_files', function (Blueprint $table) {
            $table->dropColumn(['records_count', 'is_completed']);
        });
    }
};