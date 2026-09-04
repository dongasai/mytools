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
        Schema::create('application_system_logs', function (Blueprint $table) {
            $table->id()->comment('主键ID');
            $table->string('level1', 50)->nullable()->comment('来源类型（fund, item, farm等）');
            $table->text('message')->comment('日志消息内容');
            $table->timestamp('created_at')->nullable()->useCurrent()->comment('创建时间（兼容字段，等同于collected_at）');
            $table->text('data1')->comment('数据');

            // 索引
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_system_logs');
    }
};