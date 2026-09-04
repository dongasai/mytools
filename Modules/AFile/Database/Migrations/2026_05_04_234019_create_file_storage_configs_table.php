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
        Schema::create('file_storage_configs', function (Blueprint $table) {
            $table->id()->comment('主键');
            $table->string('name', 100)->comment('存储磁盘名称，唯一');
            $table->string('driver', 50)->comment('存储驱动（local, s3, oss等）');
            $table->text('config')->comment('配置值，JSON格式');
            $table->string('description', 500)->default('')->comment('配置描述');
            $table->tinyInteger('is_default')->default(0)->comment('是否默认存储，1表示是，0表示否');
            $table->tinyInteger('is_temp')->default(0)->comment('是否用于临时存储，1表示是，0表示否');
            $table->tinyInteger('status')->default(1)->comment('状态：1-启用，0-禁用');
            $table->string('env', 50)->default('production')->comment('环境（development, testing, production）');
            $table->timestamps();
            $table->unsignedInteger('created_by')->default(0)->comment('创建人ID');
            $table->unsignedInteger('updated_by')->default(0)->comment('更新人ID');

            // 索引
            $table->unique(['name', 'env'], 'idx_name_env');
            $table->index('status', 'idx_status');
            $table->index('env', 'idx_env');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_storage_configs');
    }
};
