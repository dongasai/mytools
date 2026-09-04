<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SSH认证方式表迁移
 *
 * @author AI开发团队
 * @date 2026-09-02
 */
return new class extends Migration
{
    /**
     * 表名
     */
    private string $tableName = 'fssh_authentications';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('认证名称');
            $table->unsignedBigInteger('server_id')->comment('服务器ID');
            $table->enum('auth_type', ['key', 'password', 'certificate', 'agent'])->default('key')->comment('认证类型');
            $table->text('credentials')->comment('凭证信息（加密存储JSON）');
            $table->boolean('is_default')->default(false)->comment('是否默认认证');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('状态');
            $table->string('description', 500)->nullable()->comment('描述');
            $table->timestamps();
            $table->softDeletes();

            // 外键
            $table->foreign('server_id', 'fk_server_id')
                ->references('id')
                ->on('fssh_servers')
                ->onDelete('cascade');

            // 索引
            $table->index('server_id', 'idx_server_id');
            $table->index('auth_type', 'idx_auth_type');
            $table->index('is_default', 'idx_is_default');
            $table->index('status', 'idx_status');
        });

        // 表注释
        DB::statement("ALTER TABLE `{$this->tableName}` COMMENT = 'SSH认证方式表'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists($this->tableName);
    }
};