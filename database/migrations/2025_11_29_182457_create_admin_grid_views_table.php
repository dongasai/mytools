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
        // 删除已存在的 admin_grid_views 表（如果存在）
        Schema::dropIfExists('admin_grid_views');
        
        // 创建 admin_grid_views 表
        Schema::create('admin_grid_views', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id')->comment('操作的Admin ID');
            $table->enum('type1', ['private', 'public'])->default('private')->comment('视图类型：私有/公共');
            $table->string('title')->comment('视图标题');
            $table->string('router_name')->comment('路由名字');
            $table->json('p1')->nullable()->comment('参数1 (JSON格式)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_grid_views');
    }
};