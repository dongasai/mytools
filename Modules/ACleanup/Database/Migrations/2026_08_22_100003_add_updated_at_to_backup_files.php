<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 添加updated_at字段到cleanup_backup_files
 */
return new class extends Migration
{
    /**
     * 运行迁移
     */
    public function up(): void
    {
        Schema::table('cleanup_backup_files', function (Blueprint $table) {
            $table->timestamp('updated_at')->nullable()->after('created_at');
        });
    }

    /**
     * 回滚迁移
     */
    public function down(): void
    {
        Schema::table('cleanup_backup_files', function (Blueprint $table) {
            $table->dropColumn('updated_at');
        });
    }
};