<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SSH服务器表迁移
 *
 * @author AI开发团队
 * @date 2026-09-02
 */
return new class extends Migration
{
    /**
     * 表名
     */
    private string $tableName = 'fssh_servers';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('服务器名称');
            $table->unsignedBigInteger('group_id')->nullable()->comment('分组ID');
            $table->string('host', 255)->comment('主机地址');
            $table->unsignedSmallInteger('port')->default(22)->comment('SSH端口');
            $table->enum('system_type', ['linux', 'windows', 'macos'])->default('linux')->comment('系统类型');
            $table->json('system_info')->nullable()->comment('系统信息');
            $table->enum('status', ['active', 'inactive', 'offline'])->default('active')->comment('状态');
            $table->timestamp('last_check_at')->nullable()->comment('最后检测时间');
            $table->string('description', 500)->nullable()->comment('描述');
            $table->timestamps();
            $table->softDeletes();

            // 外键
            $table->foreign('group_id', 'fk_group_id')
                ->references('id')
                ->on('fssh_server_groups')
                ->onDelete('set null');

            // 索引
            $table->index('group_id', 'idx_group_id');
            $table->index('status', 'idx_status');
            $table->index('host', 'idx_host');
        });

        // 表注释
        DB::statement("ALTER TABLE `{$this->tableName}` COMMENT = 'SSH服务器表'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists($this->tableName);
    }
};