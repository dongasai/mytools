<?php

declare(strict_types=1);

namespace Modules\FeatureSsh\Enums;

/**
 * SSH认证类型枚举
 *
 * @author AI开发团队
 * @date 2026-09-02
 */
enum AuthType: string
{
    /**
     * 密钥认证
     */
    case KEY = 'key';

    /**
     * 密码认证
     */
    case PASSWORD = 'password';

    /**
     * 证书认证
     */
    case CERTIFICATE = 'certificate';

    /**
     * SSH Agent认证
     */
    case AGENT = 'agent';

    /**
     * 获取标签
     */
    public function label(): string
    {
        return match ($this) {
            self::KEY => '密钥认证',
            self::PASSWORD => '密码认证',
            self::CERTIFICATE => '证书认证',
            self::AGENT => 'SSH Agent',
        };
    }

    /**
     * 获取所有选项
     */
    public static function options(): array
    {
        return [
            self::KEY->value => self::KEY->label(),
            self::PASSWORD->value => self::PASSWORD->label(),
            self::CERTIFICATE->value => self::CERTIFICATE->label(),
            self::AGENT->value => self::AGENT->label(),
        ];
    }
}