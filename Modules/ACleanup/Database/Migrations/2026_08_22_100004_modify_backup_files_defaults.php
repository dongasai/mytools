<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 修改cleanup_backup_files表字段的默认值
 */
return new class extends Migration
{
    /**
     * 运行迁移
     */
    public function up(): void
    {
        Schema::table('cleanup_backup_files', function (Blueprint $table) {
            $table->integer('backup_type')->default(1)->comment('备份类型:1SQL,2JSON,3CSV')->change();
            $table->integer('compression_type')->default(1)->comment('压缩类型:1none,2gzip,3zip')->change();
        });
    }

    /**
     * 回滚迁移
     */
    public function down(): void
    {
        Schema::table('cleanup_backup_files', function (Blueprint $table) {
            $table->integer('backup_type')->default(null)->change();
            $table->integer('compression_type')->default(null)->change();
        });
    }
};