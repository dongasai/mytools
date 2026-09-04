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
        Schema::create('ai_test_results', function (Blueprint $table) {
            $table->id()->comment('主键ID');

            // 关联字段
            $table->unsignedBigInteger('test_id')->comment('测试记录ID');
            $table->unsignedInteger('test_sequence')->default(1)->comment('测试序号');

            // 测试结果
            $table->unsignedTinyInteger('is_success')->default(0)->comment('是否成功:1成功,2失败');
            $table->unsignedInteger('response_time_ms')->default(0)->comment('响应时间(毫秒)');

            // tokens和成本
            $table->unsignedInteger('input_tokens')->default(0)->comment('输入tokens');
            $table->unsignedInteger('output_tokens')->default(0)->comment('输出tokens');
            $table->decimal('cost', 10, 6)->default(0.000000)->comment('成本(美元)');

            // 响应内容
            $table->text('response_text')->nullable()->comment('响应文本');
            $table->text('error_message')->nullable()->comment('错误信息');

            $table->timestamp('created_at')->useCurrent()->comment('创建时间');

            // 索引
            $table->index('test_id', 'idx_test_id');
            $table->index('is_success', 'idx_is_success');

            $table->comment('AI测试结果详情表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_test_results');
    }
};
