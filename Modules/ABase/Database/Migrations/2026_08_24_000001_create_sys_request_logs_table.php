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
        Schema::create('sys_request_logs', function (Blueprint $table) {
            $table->id()->comment('主键ID');
            $table->string('unid', 100)->unique()->comment('请求唯一ID');
            $table->string('request_unid', 100)->nullable()->comment('请求UNID');
            $table->string('run_unid', 100)->nullable()->comment('运行UNID');

            $table->string('path')->index()->comment('请求路径');
            $table->string('method', 10)->comment('请求方法');
            $table->string('router')->nullable()->comment('路由');
            $table->string('module', 50)->nullable()->index()->comment('模块');

            $table->text('headers')->nullable()->comment('请求头JSON');
            $table->text('query')->nullable()->comment('Query参数JSON');
            $table->longText('post')->nullable()->comment('POST数据JSON');
            $table->longText('protobuf_json')->nullable()->comment('Protobuf JSON数据');
            $table->text('files')->nullable()->comment('上传文件信息JSON');

            $table->string('ipaddress', 50)->nullable()->comment('IP地址');
            $table->string('host')->nullable()->comment('主机');
            $table->text('user_agent')->nullable()->comment('User Agent');
            $table->unsignedBigInteger('user_id')->default(0)->index()->comment('用户ID');
            $table->string('token', 100)->nullable()->index()->comment('Token');

            $table->string('response_status', 10)->nullable()->comment('响应状态码');
            $table->string('response_type', 100)->nullable()->comment('响应Content-Type');
            $table->unsignedInteger('response_size')->default(0)->comment('响应大小(字节)');
            $table->longText('response')->nullable()->comment('响应内容');
            $table->boolean('response_truncated')->default(false)->comment('响应是否截断');

            $table->text('error')->nullable()->comment('错误信息');

            $table->unsignedInteger('run_ms')->default(0)->comment('运行毫秒数');
            $table->unsignedInteger('sql_num')->default(0)->comment('SQL查询次数');

            $table->index('created_at');
            $table->timestamps();

            $table->comment('请求日志表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sys_request_logs');
    }
};