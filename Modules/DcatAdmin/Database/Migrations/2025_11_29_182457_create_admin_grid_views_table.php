<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 创建 admin_grid_views 表
 *
 * 后台视图记录表，用于记录管理员访问的视图信息
 */
return new class extends Migration
{
    /**
     * 执行迁移
     */
    public function up(): void
    {
        Schema::create('admin_grid_views', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id')->comment('操作的Admin ID');
            $table->enum('type1', ['private', 'public'])->default('private')->comment('视图类型：私有/公共');
            $table->string('title')->comment('视图标题');
            $table->string('router_name')->comment('路由名字');
            $table->json('p1')->nullable()->comment('参数1 (JSON格式)');
            $table->timestamps();

            // 索引
            $table->index('admin_id');
            $table->index('type1');
            $table->index('router_name');
        });

        // 添加表注释
        Schema::table('admin_grid_views', function ($table) {
            $table->comment = '后台视图记录表';
        });
    }

    /**
     * 回滚迁移
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_grid_views');
    }
};