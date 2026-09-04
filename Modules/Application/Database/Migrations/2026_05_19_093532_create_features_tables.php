<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * 创建功能开关系统相关表:
     * 1. features - 功能定义表
     * 2. user_feature_settings - 用户个人开关表
     * 3. feature_whitelist - 功能白名单表
     * 4. feature_blacklist - 功能黑名单表
     */
    public function up(): void
    {
        // 1. features表(功能定义表)
        Schema::create('features', function (Blueprint $table) {
            $table->id()->comment('功能ID');
            $table->string('name', 100)->comment('功能名称');
            $table->string('key', 100)->unique()->comment('功能标识(唯一)');
            $table->text('description')->nullable()->comment('功能描述');
            $table->boolean('is_enabled')->default(false)->comment('默认状态:0关闭 1开启');
            $table->integer('percentage')->default(0)->comment('灰度百分比(0-100)');
            $table->string('group', 50)->nullable()->comment('功能分组(创作类/社交类/AI类)');
            $table->timestamps();

            $table->index('group');
        });

        // 2. user_feature_settings表(用户个人开关表)
        Schema::create('user_feature_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('用户ID');
            $table->unsignedBigInteger('feature_id')->comment('功能ID');
            $table->boolean('is_enabled')->default(false)->comment('开关状态:0关闭 1开启');
            $table->timestamps();

            $table->unique(['user_id', 'feature_id'], 'uk_user_feature');
            $table->index('user_id');
            $table->index('feature_id');
        });

        // 3. feature_whitelist表(白名单表)
        Schema::create('feature_whitelist', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feature_id')->comment('功能ID');
            $table->unsignedBigInteger('user_id')->comment('用户ID');
            $table->string('reason', 255)->nullable()->comment('加入白名单原因');
            $table->timestamps();

            $table->unique(['feature_id', 'user_id'], 'uk_feature_user');
            $table->index('feature_id');
            $table->index('user_id');
        });

        // 4. feature_blacklist表(黑名单表)
        Schema::create('feature_blacklist', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feature_id')->comment('功能ID');
            $table->unsignedBigInteger('user_id')->comment('用户ID');
            $table->string('reason', 255)->nullable()->comment('加入黑名单原因');
            $table->timestamps();

            $table->unique(['feature_id', 'user_id'], 'uk_blacklist_feature_user');
            $table->index('feature_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_blacklist');
        Schema::dropIfExists('feature_whitelist');
        Schema::dropIfExists('user_feature_settings');
        Schema::dropIfExists('features');
    }
};
