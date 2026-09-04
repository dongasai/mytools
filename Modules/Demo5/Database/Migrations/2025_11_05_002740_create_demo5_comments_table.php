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
        Schema::create('demo5_comments', function (Blueprint $table) {
            $table->id();
            $table->text('content')->comment('评论内容');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->comment('评论状态');
            $table->unsignedBigInteger('post_id')->comment('文章ID');
            $table->unsignedBigInteger('user_id')->comment('评论者ID');
            $table->unsignedBigInteger('parent_id')->nullable()->comment('父评论ID');
            $table->string('ip_address', 45)->nullable()->comment('IP地址');
            $table->text('user_agent')->nullable()->comment('用户代理');
            $table->timestamps();

            // 索引
            $table->index('status');
            $table->index('post_id');
            $table->index('user_id');
            $table->index('parent_id');
            $table->index('created_at');

            // 注意：移除外键约束，在应用层处理数据一致性
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demo5_comments');
    }
};
