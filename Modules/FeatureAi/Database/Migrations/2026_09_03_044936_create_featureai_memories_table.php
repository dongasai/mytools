<?php

/**
 * 记忆表迁移
 *
 * 存储AI系统的记忆数据
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * 创建featureai_memories表
     * 用于存储AI系统的长期记忆数据
     */
    public function up(): void
    {
        if (!Schema::hasTable('featureai_memories')) {
            Schema::create('featureai_memories', function (Blueprint $table) {
                $table->id()->comment('主键ID');

                // 分类字段
                $table->unsignedTinyInteger('category')->default(1)->comment('记忆分类:1项目级别,2工作空间级别,3用户级别');
                $table->string('context', 255)->comment('记忆上下文标识');

                // 内容字段
                $table->json('content')->comment('记忆内容JSON');
                $table->json('tags')->nullable()->comment('标签列表JSON');
                $table->unsignedTinyInteger('importance')->default(5)->comment('重要程度:1-10，默认5');

                $table->timestamps();

                // 索引
                $table->index('category', 'idx_category');
                $table->index('context', 'idx_context');
                $table->index('importance', 'idx_importance');
                $table->index(['category', 'context'], 'idx_category_context');
                $table->index(['category', 'importance'], 'idx_category_importance');
                $table->index('created_at', 'idx_created_at');

                $table->comment('FeatureAi模块-AI记忆表');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * 删除featureai_memories表
     */
    public function down(): void
    {
        Schema::dropIfExists('featureai_memories');
    }
};
