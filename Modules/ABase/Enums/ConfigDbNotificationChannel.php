<?php

namespace Modules\ABase\Enums;

/**
 * 配置表备份通知渠道枚举
 */
enum ConfigDbNotificationChannel: string
{
    case LOG = 'log';
    case EMAIL = 'email';
    case SLACK = 'slack';
    case WEBHOOK = 'webhook';
    case DATABASE = 'database';

    /**
     * 获取渠道描述
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::LOG => '日志记录 - 写入应用日志',
            self::EMAIL => '邮件通知 - 发送邮件通知',
            self::SLACK => 'Slack通知 - 发送Slack消息',
            self::WEBHOOK => 'Webhook - 调用HTTP接口',
            self::DATABASE => '数据库 - 记录到数据库表',
        };
    }

    /**
     * 获取渠道图标
     */
    public function getIcon(): string
    {
        return match ($this) {
            self::LOG => '📝',
            self::EMAIL => '📧',
            self::SLACK => '💬',
            self::WEBHOOK => '🔗',
            self::DATABASE => '🗄️',
        };
    }

    /**
     * 获取配置要求
     */
    public function getRequiredConfig(): array
    {
        return match ($this) {
            self::LOG => [
                'log_level' => 'info|warning|error',
                'log_channel' => 'configdb',
            ],
            self::EMAIL => [
                'recipients' => 'array',
                'subject' => 'string',
                'template' => 'string',
            ],
            self::SLACK => [
                'webhook_url' => 'string',
                'channel' => 'string',
                'username' => 'string',
            ],
            self::WEBHOOK => [
                'url' => 'string',
                'method' => 'POST|GET',
                'headers' => 'array',
                'timeout' => 'integer',
            ],
            self::DATABASE => [
                'table' => 'string',
                'connection' => 'string',
            ],
        };
    }

    /**
     * 是否异步处理
     */
    public function isAsync(): bool
    {
        return match ($this) {
            self::LOG => false,
            self::DATABASE => false,
            self::EMAIL => true,
            self::SLACK => true,
            self::WEBHOOK => true,
        };
    }

    /**
     * 获取示例配置
     */
    public function getExampleConfig(): array
    {
        return match ($this) {
            self::LOG => [
                'log_level' => 'info',
                'log_channel' => 'configdb',
            ],
            self::EMAIL => [
                'recipients' => ['admin@example.com'],
                'subject' => '配置表备份完成通知',
                'template' => 'emails.configdb_backup',
            ],
            self::SLACK => [
                'webhook_url' => 'https://hooks.slack.com/services/...',
                'channel' => '#backups',
                'username' => 'Backup Bot',
            ],
            self::WEBHOOK => [
                'url' => 'https://api.example.com/webhooks/backup',
                'method' => 'POST',
                'headers' => ['Authorization' => 'Bearer token'],
                'timeout' => 30,
            ],
            self::DATABASE => [
                'table' => 'backup_notifications',
                'connection' => 'mysql',
            ],
        };
    }

    /**
     * 验证通知渠道是否有效
     */
    public static function isValid(string $channel): bool
    {
        return in_array($channel, array_column(self::cases(), 'value'));
    }

    /**
     * 获取所有可用通知渠道
     */
    public static function getAllChannels(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'description' => $case->getDescription(),
            'icon' => $case->getIcon(),
            'is_async' => $case->isAsync(),
            'required_config' => $case->getRequiredConfig(),
            'example_config' => $case->getExampleConfig(),
        ], self::cases());
    }
}
