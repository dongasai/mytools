<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SSH命令执行日志表迁移
 *
 * @author AI开发团队
 * @date 2026-09-02
 */
return new class extends Migration
{
    /**
     * 表名
     */
    private string $tableName = 'fssh_command_logs';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('server_id')->comment('服务器ID');
            $table->unsignedBigInteger('user_id')->nullable()->comment('执行用户ID');
            $table->string('command', 1000)->comment('执行的命令');
            $table->smallInteger('exit_code')->nullable()->comment('退出码');
            $table->text('output')->nullable()->comment('命令输出（截断存储）');
            $table->unsignedInteger('execution_time')->default(0)->comment('执行时间（毫秒）');
            $table->enum('status', ['success', 'failed', 'timeout'])->default('success')->comment('执行状态');
            $table->timestamp('executed_at')->nullable()->comment('执行时间');
            $table->timestamps();

            // 外键
            $table->foreign('server_id', 'fk_server_id')
                ->references('id')
                ->on('fssh_servers')
                ->onDelete('cascade');

            // 索引
            $table->index('server_id', 'idx_server_id');
            $table->index('user_id', 'idx_user_id');
            $table->index('status', 'idx_status');
            $table->index('executed_at', 'idx_executed_at');
        });

        // 表注释
        DB::statement("ALTER TABLE `{$this->tableName}` COMMENT = 'SSH命令执行日志表'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists($this->tableName);
    }
};