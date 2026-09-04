<?php

namespace Modules\Application\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 创建failed_jobs表迁移
 *
 * 队列失败任务记录表
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
        if (!Schema::hasTable("failed_jobs")) {
            Schema::create("failed_jobs", function (Blueprint $table) {
                $table->id();
                $table->string("uuid")->unique();
                $table->text("connection");
                $table->text("queue");
                $table->longText("payload");
                $table->longText("exception");
                $table->timestamp("failed_at")->useCurrent();

                $table->engine = "InnoDB";
                $table->charset = "utf8mb4";
                $table->collation = "utf8mb4_unicode_ci";
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
        Schema::dropIfExists("failed_jobs");
    }
};
