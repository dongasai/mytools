<?php

declare(strict_types=1);

namespace Modules\FeatureSsh\Enums;

/**
 * 系统类型枚举
 *
 * @author AI开发团队
 * @date 2026-09-02
 */
enum SystemType: string
{
    /**
     * Linux系统
     */
    case LINUX = 'linux';

    /**
     * Windows系统
     */
    case WINDOWS = 'windows';

    /**
     * macOS系统
     */
    case MACOS = 'macos';

    /**
     * 获取标签
     */
    public function label(): string
    {
        return match ($this) {
            self::LINUX => 'Linux',
            self::WINDOWS => 'Windows',
            self::MACOS => 'macOS',
        };
    }

    /**
     * 获取所有选项
     */
    public static function options(): array
    {
        return [
            self::LINUX->value => self::LINUX->label(),
            self::WINDOWS->value => self::WINDOWS->label(),
            self::MACOS->value => self::MACOS->label(),
        ];
    }
}