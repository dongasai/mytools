<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * 创建 Demo5 模块用户表
     */
    public function up(): void
    {
        Schema::create('demo5_users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('用户姓名');
            $table->string('email')->unique()->comment('用户邮箱');
            $table->string('phone')->nullable()->comment('用户手机号');
            $table->string('avatar')->nullable()->comment('用户头像');
            $table->enum('status', ['active', 'inactive', 'banned'])->default('active')->comment('用户状态');
            $table->timestamp('last_login_at')->nullable()->comment('最后登录时间');
            $table->timestamps();

            // 索引
            $table->index('status');
            $table->index('email');

            // 注意：不建立外键约束，保持模块独立性
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demo5_users');
    }
};