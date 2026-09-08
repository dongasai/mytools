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
        Schema::create('cleanup_configs', function (Blueprint $table) {
            $table->id()->comment('主键ID');
            $table->string('table_name', 100)->comment('表名');
            $table->string('model_class')->nullable()->comment('Model类名');
            $table->json('model_info')->nullable()->comment('Model类信息');
            $table->string('module_name', 50)->comment('模块名称');
            $table->unsignedTinyInteger('data_category')->comment('数据分类:1用户数据,2日志数据,3交易数据,4缓存数据,5配置数据');
            $table->unsignedTinyInteger('default_cleanup_type')->comment('默认清理类型:1清空表,2删除所有,3按时间删除,4按用户删除,5按条件删除');
            $table->json('default_conditions')->nullable()->comment('默认清理条件JSON配置');
            $table->boolean('is_enabled')->default(1)->comment('是否启用清理');
            $table->unsignedInteger('priority')->default(100)->comment('清理优先级(数字越小优先级越高)');
            $table->unsignedInteger('batch_size')->default(1000)->comment('批处理大小');
            $table->text('description')->nullable()->comment('配置描述');
            $table->timestamp('last_cleanup_at')->nullable()->comment('最后清理时间');
            $table->timestamps();

            // 索引
            $table->unique('table_name'); // 使用默认索引名 cleanup_configs_table_name_unique
            $table->index(['module_name', 'data_category']); // 使用默认索引名
            $table->index(['is_enabled', 'priority']);
            $table->index('last_cleanup_at');
            $table->index('model_class');

            $table->comment('清理配置表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cleanup_configs');
    }
};