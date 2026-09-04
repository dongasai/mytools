<?php

declare(strict_types=1);

namespace Modules\FeatureSsh\Enums;

/**
 * SSH服务器状态枚举
 *
 * @author AI开发团队
 * @date 2026-09-02
 */
enum ServerStatus: string
{
    /**
     * 活跃
     */
    case ACTIVE = 'active';

    /**
     * 未激活
     */
    case INACTIVE = 'inactive';

    /**
     * 离线
     */
    case OFFLINE = 'offline';

    /**
     * 获取标签
     */
    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => '活跃',
            self::INACTIVE => '未激活',
            self::OFFLINE => '离线',
        };
    }

    /**
     * 获取所有选项
     */
    public static function options(): array
    {
        return [
            self::ACTIVE->value => self::ACTIVE->label(),
            self::INACTIVE->value => self::INACTIVE->label(),
            self::OFFLINE->value => self::OFFLINE->label(),
        ];
    }

    /**
     * 获取颜色（用于Dcat Admin）
     */
    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::INACTIVE => 'warning',
            self::OFFLINE => 'danger',
        };
    }
}