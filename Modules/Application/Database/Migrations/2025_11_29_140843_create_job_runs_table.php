<?php

namespace Modules\Application\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 创建job_runs表迁移
 *
 * 队列任务运行记录表
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
        if (!Schema::hasTable("job_runs")) {
            Schema::create("job_runs", function (Blueprint $table) {
                $table->id();
                $table->string("queue");
                $table->longText("payload");
                $table->string("runclass", 1000)->nullable()->comment("运行类");
                $table->tinyInteger("attempts")->unsigned();
                $table->integer("reserved_at")->unsigned()->nullable();
                $table->integer("available_at")->unsigned();
                $table->integer("created_at")->unsigned();
                $table->string("status", 100)->nullable()->comment("运行状态");
                $table->text("desc")->nullable()->comment("描述信息");
                $table
                    ->decimal("runtime", 12, 5)
                    ->default(0.0)
                    ->comment("运行时间");

                // 索引
                $table->index("queue");
                $table->index(["created_at", "status"]);

                $table->engine = "InnoDB";
                $table->charset = "utf8mb4";
                $table->collation = "utf8mb4_unicode_ci";
            });

            // 添加表注释
            Schema::table("job_runs", function ($table) {
                $table->comment = "队列任务运行记录表";
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
        Schema::dropIfExists("job_runs");
    }
};
