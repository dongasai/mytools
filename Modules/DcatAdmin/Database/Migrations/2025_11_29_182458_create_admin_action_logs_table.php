<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 创建 admin_action_logs 表
 *
 * 后台操作日志表，用于记录管理员的所有操作行为
 */
return new class extends Migration
{
    /**
     * 执行迁移
     */
    public function up(): void
    {
        Schema::create('admin_action_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type1')->comment('操作类型');
            $table->string('unid', 64)->unique()->comment('唯一标识符');
            $table->unsignedBigInteger('admin_id')->comment('操作的Admin ID');
            $table->string('object_class')->comment('操作对象类名');
            $table->string('url')->comment('操作URL');
            $table->json('before')->nullable()->comment('操作之前的数据 (JSON格式)');
            $table->json('after')->nullable()->comment('操作之后的数据 (JSON格式)');
            $table->tinyInteger('status')->default(1)->comment('状态：1-成功，0-失败');
            $table->string('p1')->nullable()->comment('参数1');
            $table->timestamps();

            // 索引
            $table->index('admin_id');
            $table->index('type1');
            $table->index('unid');
            $table->index('object_class');
            $table->index('status');
            $table->index('created_at');
        });

        // 添加表注释
        Schema::table('admin_action_logs', function ($table) {
            $table->comment = '后台操作日志表';
        });
    }

    /**
     * 回滚迁移
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_action_logs');
    }
};