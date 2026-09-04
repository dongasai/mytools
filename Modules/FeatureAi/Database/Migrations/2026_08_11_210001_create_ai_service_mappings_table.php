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
        Schema::create('ai_service_mappings', function (Blueprint $table) {
            $table->id()->comment('主键ID');

            // 服务映射信息
            $table->string('service_type', 50)->comment('服务类型，如a/b等');
            $table->string('service_name', 100)->default('')->comment('服务名字，如name1/name2等，空字符串表示默认');
            $table->unsignedBigInteger('provider_id')->comment('关联ai_providers.id');

            // 状态和描述
            $table->unsignedTinyInteger('is_active')->default(1)->comment('是否启用:1启用,2禁用');
            $table->string('description', 255)->nullable()->comment('映射说明');

            $table->timestamps();
            $table->softDeletes()->comment('软删除时间');

            // 索引
            $table->index('service_type', 'idx_service_type');
            $table->index('provider_id', 'idx_provider_id');
            $table->index('is_active', 'idx_is_active');
            $table->index('deleted_at', 'idx_deleted_at');

            // 唯一索引：service_type + service_name 组合唯一
            // 使用空字符串而非NULL，因为MySQL UNIQUE对NULL不生效（允许多条NULL值）
            $table->unique(['service_type', 'service_name'], 'uk_service_type_name');

            $table->comment('AI服务映射表：服务类型+服务名字→供应商映射关系');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_service_mappings');
    }
};
