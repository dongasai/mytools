<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SSH服务器分组表迁移
 *
 * @author AI开发团队
 * @date 2026-09-02
 */
return new class extends Migration
{
    /**
     * 表名
     */
    private string $tableName = 'fssh_server_groups';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('分组名称');
            $table->unsignedBigInteger('parent_id')->nullable()->comment('父分组ID');
            $table->string('description', 500)->nullable()->comment('描述');
            $table->timestamps();
            $table->softDeletes();

            // 索引
            $table->index('parent_id', 'idx_parent_id');
            $table->index('name', 'idx_name');
        });

        // 表注释
        DB::statement("ALTER TABLE `{$this->tableName}` COMMENT = 'SSH服务器分组表'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists($this->tableName);
    }
};