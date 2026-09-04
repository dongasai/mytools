<?php

namespace Modules\Application\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 创建application_continuous_times表迁移
 *
 * 连续次数判定表
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
        if (!Schema::hasTable("application_continuous_times")) {
            Schema::create("application_continuous_times", function (Blueprint $table) {
                $table->id();
                $table
                    ->unsignedBigInteger("user_id")
                    ->nullable()
                    ->comment("用户id");
                $table->string("stype", 100)->nullable()->comment("产品类型");
                $table->bigInteger("sid")->nullable()->comment("产品id");
                $table->bigInteger("number")->nullable()->comment("计数");
                $table
                    ->bigInteger("last_time")
                    ->nullable()
                    ->comment("最后的时间");
                $table->timestamps();
                $table->softDeletes();
                $table->integer("diff")->nullable()->comment("差值");

                // 索引
                $table->index("user_id");
                $table->index(["stype", "sid"]);
                $table->index("last_time");

                $table->engine = "InnoDB";
                $table->charset = "utf8mb4";
                $table->collation = "utf8mb4_general_ci";
            });

            // 添加表注释
            Schema::table("application_continuous_times", function ($table) {
                $table->comment = "连续次数判定";
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
        Schema::dropIfExists("application_continuous_times");
    }
};
