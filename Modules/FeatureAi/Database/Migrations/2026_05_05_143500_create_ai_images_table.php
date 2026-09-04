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
        Schema::create('ai_images', function (Blueprint $table) {
            $table->id()->comment('主键ID');

            // 关联字段
            $table->unsignedBigInteger('provider_id')->comment('提供商ID');
            $table->unsignedBigInteger('model_id')->comment('模型ID');
            $table->unsignedBigInteger('user_id')->comment('用户ID');

            // 图片生成内容
            $table->text('prompt_text')->comment('图片生成提示文本');
            $table->string('image_url', 500)->nullable()->comment('生成的图片URL');
            $table->string('image_path', 255)->nullable()->comment('图片存储路径(本地或云端)');
            $table->string('image_size', 50)->nullable()->comment('图片尺寸(如1024x1024)');

            // 成本
            $table->decimal('cost', 10, 6)->default(0.000000)->comment('生成成本(美元)');

            // 状态
            $table->unsignedTinyInteger('status')->default(1)->comment('状态:1待处理,2生成中,3成功,4失败');
            $table->text('error_message')->nullable()->comment('错误信息');
            $table->unsignedTinyInteger('retry_count')->default(0)->comment('重试次数');

            $table->timestamps();
            $table->softDeletes()->comment('软删除时间');

            // 索引
            $table->index('provider_id', 'idx_provider_id');
            $table->index('model_id', 'idx_model_id');
            $table->index('user_id', 'idx_user_id');
            $table->index('status', 'idx_status');
            $table->index('deleted_at', 'idx_deleted_at');

            $table->comment('AI图片生成记录表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_images');
    }
};
