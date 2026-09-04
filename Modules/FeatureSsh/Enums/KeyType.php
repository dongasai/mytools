<?php

declare(strict_types=1);

namespace Modules\FeatureSsh\Enums;

/**
 * 密钥类型枚举
 *
 * @author AI开发团队
 * @date 2026-09-02
 */
enum KeyType: string
{
    /**
     * RSA密钥
     */
    case RSA = 'rsa';

    /**
     * ED25519密钥
     */
    case ED25519 = 'ed25519';

    /**
     * ECDSA密钥
     */
    case ECDSA = 'ecdsa';

    /**
     * 获取标签
     */
    public function label(): string
    {
        return match ($this) {
            self::RSA => 'RSA',
            self::ED25519 => 'ED25519',
            self::ECDSA => 'ECDSA',
        };
    }

    /**
     * 获取所有选项
     */
    public static function options(): array
    {
        return [
            self::ED25519->value => self::ED25519->label() . ' (推荐)',
            self::RSA->value => self::RSA->label(),
            self::ECDSA->value => self::ECDSA->label(),
        ];
    }
}