<?php

namespace Modules\DcatAdmin\Enums;

/**
 * 管理员操作类型枚举
 */
enum ADMIN_ACTION_TYPE: string
{
    case LOGIN = 'LOGIN';                    // 登录
    case LOGOUT = 'LOGOUT';                  // 登出
    case CREATE = 'CREATE';                  // 创建
    case UPDATE = 'UPDATE';                  // 更新
    case DELETE = 'DELETE';                  // 删除
    case VIEW = 'VIEW';                      // 查看
    case EXPORT = 'EXPORT';                  // 导出
    case IMPORT = 'IMPORT';                  // 导入
    case CACHE_CLEAR = 'CACHE_CLEAR';        // 清理缓存
    case BACKUP = 'BACKUP';                  // 备份
    case RESTORE = 'RESTORE';                // 恢复
    case MAINTENANCE = 'MAINTENANCE';        // 维护
    case CONFIG_CHANGE = 'CONFIG_CHANGE';    // 配置变更
    case PERMISSION_CHANGE = 'PERMISSION_CHANGE'; // 权限变更
    case PASSWORD_RESET = 'PASSWORD_RESET';  // 密码重置
    case SYSTEM_RESTART = 'SYSTEM_RESTART';  // 系统重启

    /**
     * 获取操作类型的中文描述
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::LOGIN => '登录',
            self::LOGOUT => '登出',
            self::CREATE => '创建',
            self::UPDATE => '更新',
            self::DELETE => '删除',
            self::VIEW => '查看',
            self::EXPORT => '导出',
            self::IMPORT => '导入',
            self::CACHE_CLEAR => '清理缓存',
            self::BACKUP => '备份',
            self::RESTORE => '恢复',
            self::MAINTENANCE => '维护',
            self::CONFIG_CHANGE => '配置变更',
            self::PERMISSION_CHANGE => '权限变更',
            self::PASSWORD_RESET => '密码重置',
            self::SYSTEM_RESTART => '系统重启',
        };
    }

    /**
     * 获取操作类型的颜色
     */
    public function getColor(): string
    {
        return match ($this) {
            self::LOGIN => 'success',
            self::LOGOUT => 'info',
            self::CREATE => 'primary',
            self::UPDATE => 'warning',
            self::DELETE => 'danger',
            self::VIEW => 'secondary',
            self::EXPORT => 'info',
            self::IMPORT => 'info',
            self::CACHE_CLEAR => 'warning',
            self::BACKUP => 'primary',
            self::RESTORE => 'warning',
            self::MAINTENANCE => 'danger',
            self::CONFIG_CHANGE => 'warning',
            self::PERMISSION_CHANGE => 'danger',
            self::PASSWORD_RESET => 'warning',
            self::SYSTEM_RESTART => 'danger',
        };
    }

    /**
     * 获取操作类型的图标
     */
    public function getIcon(): string
    {
        return match ($this) {
            self::LOGIN => 'fa-sign-in-alt',
            self::LOGOUT => 'fa-sign-out-alt',
            self::CREATE => 'fa-plus',
            self::UPDATE => 'fa-edit',
            self::DELETE => 'fa-trash',
            self::VIEW => 'fa-eye',
            self::EXPORT => 'fa-download',
            self::IMPORT => 'fa-upload',
            self::CACHE_CLEAR => 'fa-broom',
            self::BACKUP => 'fa-archive',
            self::RESTORE => 'fa-undo',
            self::MAINTENANCE => 'fa-tools',
            self::CONFIG_CHANGE => 'fa-cog',
            self::PERMISSION_CHANGE => 'fa-shield-alt',
            self::PASSWORD_RESET => 'fa-key',
            self::SYSTEM_RESTART => 'fa-power-off',
        };
    }

    /**
     * 获取所有操作类型的选项数组
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
     * 判断是否为危险操作
     */
    public function isDangerous(): bool
    {
        return in_array($this, [
            self::DELETE,
            self::MAINTENANCE,
            self::PERMISSION_CHANGE,
            self::SYSTEM_RESTART,
        ]);
    }

    /**
     * 判断是否需要记录详细日志
     */
    public function needsDetailedLog(): bool
    {
        return in_array($this, [
            self::DELETE,
            self::CONFIG_CHANGE,
            self::PERMISSION_CHANGE,
            self::PASSWORD_RESET,
            self::BACKUP,
            self::RESTORE,
            self::MAINTENANCE,
            self::SYSTEM_RESTART,
        ]);
    }
}
