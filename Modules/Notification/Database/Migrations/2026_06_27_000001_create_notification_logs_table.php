<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 创建通知日志表迁移文件.
 *
 * 用于记录所有通知发送的历史记录，包括发送状态、渠道、内容等。
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * 创建 notification_logs 表.
     */
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table): void {
            // 主键
            $table->id();

            // 通知类型（关联的通知类名称）
            $table->string('notification_type', 100)->comment('通知类型');

            // 通知渠道（sms/mail/push/database）
            $table->string('channel', 20)->comment('通知渠道');

            // 多态关联（接收通知的对象）
            $table->string('notifiable_type', 100)->comment('接收对象类型');
            $table->unsignedBigInteger('notifiable_id')->comment('接收对象ID');

            // 发送状态（pending/sending/sent/failed）
            $table->string('status', 20)->default('pending')->comment('发送状态');

            // 通知数据（JSON格式）
            $table->json('data')->nullable()->comment('通知数据');

            // 错误信息（发送失败时的错误信息）
            $table->text('error_message')->nullable()->comment('错误信息');

            // 发送时间
            $table->timestamp('sent_at')->nullable()->comment('发送时间');

            // 重试次数
            $table->unsignedTinyInteger('retry_count')->default(0)->comment('重试次数');

            // 时间戳
            $table->timestamps();

            // 软删除（数据保护，避免误删）
            $table->softDeletes()->comment('软删除时间');

            // 索引
            $table->index(['status', 'created_at'], 'idx_status_created_at');
            $table->index('notification_type', 'idx_notification_type');
            $table->index('channel', 'idx_channel');

            // 多态关联索引
            $table->index(['notifiable_type', 'notifiable_id'], 'idx_notifiable');

            // 软删除索引
            $table->index('deleted_at', 'idx_deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * 删除 notification_logs 表.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};