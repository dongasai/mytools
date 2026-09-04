<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 创建通知模板表迁移文件.
 *
 * 用于管理各类通知的模板，支持多渠道模板配置。
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * 创建 notification_templates 表.
     */
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table): void {
            // 主键
            $table->id();

            // 模板名称（唯一）
            $table->string('name', 100)->unique()->comment('模板名称');

            // 关联的通知类型（Notification类名称）
            $table->string('notification_type', 100)->comment('通知类型');

            // 适用渠道（sms/mail/push/database）
            $table->string('channel', 20)->comment('适用渠道');

            // 通知标题（邮件等渠道使用）
            $table->string('subject', 200)->nullable()->comment('通知标题');

            // 通知内容模板
            $table->text('content')->comment('通知内容模板');

            // 模板变量说明（JSON格式）
            $table->json('variables')->nullable()->comment('模板变量说明');

            // 是否启用
            $table->boolean('is_active')->default(true)->comment('是否启用');

            // 模板描述
            $table->string('description', 500)->nullable()->comment('模板描述');

            // 时间戳
            $table->timestamps();

            // 索引
            $table->index('notification_type', 'idx_notification_type');
            $table->index('channel', 'idx_channel');
            $table->index('is_active', 'idx_is_active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * 删除 notification_templates 表.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};