<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 创建AI记忆表.
 *
 * 存储项目、工作区和用户级别的AI记忆数据，支持标签和重要度筛选
 */
return new class extends Migration
{
    /**
     * 运行迁移.
     */
    public function up(): void
    {
        if (Schema::hasTable('featureai_memories')) {
            return;
        }

        Schema::create('featureai_memories', function (Blueprint $table) {
            $table->id()->comment('主键ID');

            // 记忆分类
            $table->enum('category', ['project', 'workspace', 'user'])
                ->comment('记忆类别:project项目,workspace工作区,user用户');

            // 上下文信息
            $table->string('context', 255)->comment('上下文（项目名/工作区名/用户ID）');

            // 记忆内容
            $table->json('content')->comment('记忆内容');
            $table->json('tags')->nullable()->comment('标签');
            $table->unsignedTinyInteger('importance')->default(5)->comment('重要程度 1-10');

            $table->timestamps();

            // 索引
            $table->index('category', 'idx_category');
            $table->index('context', 'idx_context');
            $table->index('importance', 'idx_importance');
            $table->index(['category', 'context'], 'idx_category_context');
            $table->index(['category', 'importance'], 'idx_category_importance');
            $table->index('created_at', 'idx_created_at');
        });

        // 添加表注释
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `featureai_memories` COMMENT='AI记忆表'");
        }
    }

    /**
     * 回滚迁移.
     */
    public function down(): void
    {
        Schema::dropIfExists('featureai_memories');
    }
};
