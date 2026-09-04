<?php

namespace Modules\Application\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 创建application_configs表迁移
 *
 * 应用配置信息表
 *
 * @package Modules\Application\Database\Migrations
 */
return new class extends Migration {
    /**
     * 运行迁移
     *
     * @return void
     */
    public function up(): void
    {
        // 检查表是否已存在
        if (!Schema::hasTable("application_configs")) {
            Schema::create("application_configs", function (Blueprint $table) {
                $table->id();
                $table->string("keyname", 100)->comment("key");
                $table->string("is_client", 10)->comment("是否给客户端");
                $table->string("title", 100)->comment("标题");
                $table
                    ->tinyInteger("type")
                    ->unsigned()
                    ->comment("类型 详情见枚举");
                $table->string("value", 1000)->comment("值");
                $table->string("group", 200)->comment("分组");
                $table
                    ->string("group2", 200)
                    ->default("默认")
                    ->comment("分组2");
                $table->timestamps();
                $table->softDeletes()->comment("删除时间");
                $table->string("desc", 100)->nullable()->comment("描述");
                $table
                    ->string("options", 1000)
                    ->nullable()
                    ->comment("其他配置");

                $table->unique("keyname");

                $table->engine = "InnoDB";
                $table->charset = "utf8mb4";
                $table->collation = "utf8mb4_unicode_ci";
            });

            // 添加表注释
            Schema::table("application_configs", function ($table) {
                $table->comment = "应用配置信息";
            });
        }
    }

    /**
     * 回滚迁移
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists("application_configs");
    }
};
