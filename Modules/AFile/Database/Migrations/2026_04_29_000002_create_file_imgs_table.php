<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_imgs', function (Blueprint $table) {
            $table->id();
            $table->string('storage_disk', 50)->default('')->comment('储存disk');
            $table->unsignedBigInteger('user_id')->default(0)->comment('用户ID');
            $table->unsignedInteger('admin_id')->default(0)->comment('管理员ID');
            $table->string('path', 500)->comment('储存目录');
            $table->string('re_type', 50)->default('')->comment('关联类型');
            $table->unsignedInteger('re_id')->default(0)->comment('关联ID');
            $table->string('o_name', 500)->comment('原名');
            $table->unsignedInteger('fsize')->default(0)->comment('文件大小');
            $table->unsignedInteger('width')->default(0)->comment('图片宽度');
            $table->unsignedInteger('height')->default(0)->comment('图片高度');
            $table->string('type1', 50)->comment('图片类型');
            $table->unsignedTinyInteger('private')->default(0)->comment('私人:0公共,1私人');
            $table->string('status', 20)->default('normal')->comment('状态:normal正常,linked已关联,dangling悬空');
            $table->timestamps();
            $table->softDeletes();

            // 索引
            $table->index('user_id');
            $table->index('status');
            $table->index(['re_type', 're_id']);
            $table->index('private');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_imgs');
    }
};
