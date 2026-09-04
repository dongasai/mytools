<?php

declare(strict_types=1);

namespace Modules\FeatureSsh\Enums;

/**
 * 命令执行状态枚举
 *
 * @author AI开发团队
 * @date 2026-09-02
 */
enum CommandStatus: string
{
    /**
     * 成功
     */
    case SUCCESS = 'success';

    /**
     * 失败
     */
    case FAILED = 'failed';

    /**
     * 超时
     */
    case TIMEOUT = 'timeout';

    /**
     * 获取标签
     */
    public function label(): string
    {
        return match ($this) {
            self::SUCCESS => '成功',
            self::FAILED => '失败',
            self::TIMEOUT => '超时',
        };
    }

    /**
     * 获取所有选项
     */
    public static function options(): array
    {
        return [
            self::SUCCESS->value => self::SUCCESS->label(),
            self::FAILED->value => self::FAILED->label(),
            self::TIMEOUT->value => self::TIMEOUT->label(),
        ];
    }

    /**
     * 获取颜色（用于Dcat Admin）
     */
    public function color(): string
    {
        return match ($this) {
            self::SUCCESS => 'success',
            self::FAILED => 'danger',
            self::TIMEOUT => 'warning',
        };
    }
}