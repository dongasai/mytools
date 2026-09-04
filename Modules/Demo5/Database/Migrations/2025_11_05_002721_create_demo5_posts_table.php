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
        Schema::create('demo5_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('文章标题');
            $table->text('content')->comment('文章内容');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft')->comment('文章状态');
            $table->unsignedBigInteger('user_id')->comment('作者ID');
            $table->timestamp('published_at')->nullable()->comment('发布时间');
            $table->timestamps();

            // 索引
            $table->index('status');
            $table->index('user_id');
            $table->index('published_at');

            // 注意：不建立外键约束，保持模块独立性
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demo5_posts');
    }
};
