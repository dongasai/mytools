<?php

namespace Modules\DcatAdmin\Enums;

/**
 * 缓存类型枚举
 */
enum CACHE_TYPE: string
{
    case CONFIG = 'CONFIG';                  // 配置缓存
    case SYSTEM = 'SYSTEM';                  // 系统缓存
    case USER = 'USER';                      // 用户缓存
    case SESSION = 'SESSION';                // 会话缓存
    case ROUTE = 'ROUTE';                    // 路由缓存
    case VIEW = 'VIEW';                      // 视图缓存
    case EVENT = 'EVENT';                    // 事件缓存
    case PERMISSION = 'PERMISSION';          // 权限缓存
    case MENU = 'MENU';                      // 菜单缓存
    case STATISTICS = 'STATISTICS';          // 统计缓存
    case TEMPORARY = 'TEMPORARY';            // 临时缓存
    case APPLICATION = 'APPLICATION';        // 应用缓存

    /**
     * 获取缓存类型的中文描述
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::CONFIG => '配置缓存',
            self::SYSTEM => '系统缓存',
            self::USER => '用户缓存',
            self::SESSION => '会话缓存',
            self::ROUTE => '路由缓存',
            self::VIEW => '视图缓存',
            self::EVENT => '事件缓存',
            self::PERMISSION => '权限缓存',
            self::MENU => '菜单缓存',
            self::STATISTICS => '统计缓存',
            self::TEMPORARY => '临时缓存',
            self::APPLICATION => '应用缓存',
        };
    }

    /**
     * 获取缓存类型的描述
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::CONFIG => '系统配置项缓存，包括数据库配置、应用配置等',
            self::SYSTEM => '系统级别的缓存，包括系统信息、状态等',
            self::USER => '用户相关的缓存，包括用户信息、偏好设置等',
            self::SESSION => '用户会话缓存，包括登录状态、临时数据等',
            self::ROUTE => 'Laravel路由缓存，提高路由解析性能',
            self::VIEW => 'Blade视图编译缓存，提高页面渲染性能',
            self::EVENT => '事件监听器缓存，提高事件处理性能',
            self::PERMISSION => '权限和角色缓存，提高权限验证性能',
            self::MENU => '后台菜单缓存，提高菜单加载性能',
            self::STATISTICS => '统计数据缓存，包括报表、图表数据等',
            self::TEMPORARY => '临时缓存，用于短期数据存储',
            self::APPLICATION => '应用级别的缓存，包括业务数据缓存',
        };
    }

    /**
     * 获取缓存类型的默认TTL（秒）
     */
    public function getDefaultTtl(): int
    {
        return match ($this) {
            self::CONFIG => 7200,      // 2小时
            self::SYSTEM => 3600,      // 1小时
            self::USER => 1800,        // 30分钟
            self::SESSION => 7200,     // 2小时
            self::ROUTE => 86400,      // 24小时
            self::VIEW => 86400,       // 24小时
            self::EVENT => 3600,       // 1小时
            self::PERMISSION => 3600,  // 1小时
            self::MENU => 7200,        // 2小时
            self::STATISTICS => 300,   // 5分钟
            self::TEMPORARY => 600,    // 10分钟
            self::APPLICATION => 1800, // 30分钟
        };
    }

    /**
     * 获取缓存类型的标签
     */
    public function getTags(): array
    {
        return match ($this) {
            self::CONFIG => ['admin', 'config'],
            self::SYSTEM => ['admin', 'system'],
            self::USER => ['user'],
            self::SESSION => ['session'],
            self::ROUTE => ['laravel', 'route'],
            self::VIEW => ['laravel', 'view'],
            self::EVENT => ['laravel', 'event'],
            self::PERMISSION => ['admin', 'permission'],
            self::MENU => ['admin', 'menu'],
            self::STATISTICS => ['admin', 'stats'],
            self::TEMPORARY => ['temp'],
            self::APPLICATION => ['app'],
        };
    }

    /**
     * 获取缓存类型的优先级
     */
    public function getPriority(): int
    {
        return match ($this) {
            self::SYSTEM => 1,         // 最高优先级
            self::CONFIG => 2,
            self::PERMISSION => 3,
            self::MENU => 4,
            self::USER => 5,
            self::SESSION => 6,
            self::ROUTE => 7,
            self::VIEW => 8,
            self::EVENT => 9,
            self::APPLICATION => 10,
            self::STATISTICS => 11,
            self::TEMPORARY => 12,     // 最低优先级
        };
    }

    /**
     * 获取所有缓存类型的选项数组
     */
    public static function getOptions(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->getLabel();
        }

        return $options;
    }

    /**
     * 判断是否为系统关键缓存
     */
    public function isCritical(): bool
    {
        return in_array($this, [
            self::SYSTEM,
            self::CONFIG,
            self::PERMISSION,
            self::SESSION,
        ]);
    }

    /**
     * 判断是否可以安全清理
     */
    public function isSafeToClear(): bool
    {
        return in_array($this, [
            self::STATISTICS,
            self::TEMPORARY,
            self::VIEW,
            self::ROUTE,
        ]);
    }
}
