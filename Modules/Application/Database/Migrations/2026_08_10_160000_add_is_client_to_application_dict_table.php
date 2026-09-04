<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 添加 is_client 字段到 application_dict 表
 *
 * 给字典表添加客户端访问控制字段，参考 application_configs 表的 is_client 设计
 */
return new class extends Migration
{
    /**
     * 执行迁移
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('application_dict', function (Blueprint $table) {
            $table->tinyInteger('is_client')
                ->default(0)
                ->after('status')
                ->comment('是否允许客户端获取(1=允许 0=不允许)');
        });

        // 给现有数据设置 is_client=1（所有现有字典数据都应该允许客户端获取）
        DB::table('application_dict')->update(['is_client' => 1]);
    }

    /**
     * 回滚迁移
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('application_dict', function (Blueprint $table) {
            $table->dropColumn('is_client');
        });
    }
};
