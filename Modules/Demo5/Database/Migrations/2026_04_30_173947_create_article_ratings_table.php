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
        Schema::create('article_ratings', function (Blueprint $table) {
            $table->id();
            $table->string('task_id')->unique()->comment('任务ID');
            $table->integer('article_id')->nullable()->comment('文章ID');
            $table->string('title')->comment('文章标题');
            $table->string('author')->comment('作者');
            $table->decimal('title_score', 5, 2)->comment('标题评分');
            $table->decimal('content_score', 5, 2)->comment('内容评分');
            $table->decimal('overall_score', 5, 2)->comment('综合评分');
            $table->string('rating', 10)->comment('评级（优秀/良好/一般/较差）');
            $table->integer('word_count')->comment('字数');
            $table->integer('char_count')->comment('字符数');
            $table->json('recommendations')->nullable()->comment('建议');
            $table->timestamp('processed_at')->comment('处理时间');
            $table->timestamps();

            $table->index('article_id');
            $table->index('overall_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_ratings');
    }
};
