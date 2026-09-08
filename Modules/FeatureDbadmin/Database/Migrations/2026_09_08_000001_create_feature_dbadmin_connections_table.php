<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('feature_dbadmin_connections', function (Blueprint $table) {
            $table->id()->comment('主键ID');

            // 连接基本信息
            $table->string('name', 100)->unique()->comment('连接名称');
            $table->string('driver', 20)->comment('驱动类型: mysql/pgsql/sqlite');
            $table->string('host', 100)->nullable()->comment('主机地址');
            $table->unsignedInteger('port')->nullable()->comment('端口号');
            $table->string('database', 100)->comment('数据库名');
            $table->string('username', 100)->nullable()->comment('用户名');
            $table->text('password')->nullable()->comment('密码');
            $table->string('charset', 20)->nullable()->comment('字符集');
            $table->string('collation', 50)->nullable()->comment('排序规则');
            $table->string('prefix', 50)->nullable()->comment('表前缀');
            $table->json('options')->nullable()->comment('其他连接选项');
            $table->text('description')->nullable()->comment('连接描述');

            // 状态信息
            $table->unsignedTinyInteger('is_active')->default(1)->comment('是否激活: 1是,0否');
            $table->timestamp('last_connected_at')->nullable()->comment('最后连接时间');
            $table->unsignedInteger('created_by')->comment('创建者ID');

            // 时间戳
            $table->timestamps();
            $table->softDeletes()->comment('软删除时间');

            // 索引
            $table->index('driver', 'idx_connections_driver');
            $table->index('is_active', 'idx_connections_is_active');
            $table->index('created_by', 'idx_connections_created_by');

            $table->comment('数据库连接配置表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_dbadmin_connections');
    }
};
