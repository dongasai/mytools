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
        Schema::create('file_storage_config_histories', function (Blueprint $table) {
            $table->id()->comment('主键');
            $table->unsignedBigInteger('config_id')->comment('关联的存储配置ID');
            $table->string('old_driver', 50)->nullable()->comment('旧存储驱动');
            $table->string('new_driver', 50)->nullable()->comment('新存储驱动');
            $table->text('old_config')->nullable()->comment('旧配置值');
            $table->text('new_config')->nullable()->comment('新配置值');
            $table->tinyInteger('old_status')->nullable()->comment('旧状态');
            $table->tinyInteger('new_status')->nullable()->comment('新状态');
            $table->timestamp('changed_at')->useCurrent()->comment('变更时间');
            $table->unsignedInteger('changed_by')->default(0)->comment('变更人ID');
            $table->string('change_reason', 500)->default('')->comment('变更原因');
            $table->timestamps();

            // 索引
            $table->index('config_id', 'idx_config_id');
            $table->index('changed_at', 'idx_changed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_storage_config_histories');
    }
};
