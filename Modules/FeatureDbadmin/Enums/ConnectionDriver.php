<?php

namespace Modules\FeatureDbadmin\Enums;

/**
 * 数据库驱动枚举
 *
 * 标识支持的数据库驱动类型，提供各驱动的默认配置信息
 */
enum ConnectionDriver: string
{
    /** MySQL 数据库驱动 */
    case MYSQL = 'mysql';

    /** PostgreSQL 数据库驱动 */
    case PGSQL = 'pgsql';

    /** SQLite 数据库驱动 */
    case SQLITE = 'sqlite';

    /**
     * 获取中文标签
     *
     * @return string 驱动类型的中文描述
     */
    public function label(): string
    {
        return match ($this) {
            self::MYSQL => 'MySQL',
            self::PGSQL => 'PostgreSQL',
            self::SQLITE => 'SQLite',
        };
    }

    /**
     * 获取默认端口
     *
     * 返回各数据库驱动的默认连接端口
     *
     * @return int 默认端口号
     */
    public function getDefaultPort(): int
    {
        return match ($this) {
            self::MYSQL => 3306,
            self::PGSQL => 5432,
            self::SQLITE => 0, // SQLite 不需要端口
        };
    }

    /**
     * 是否需要用户名密码
     *
     * 判断该数据库驱动是否需要提供用户名和密码进行认证
     *
     * @return bool 是否需要凭证
     */
    public function needsCredentials(): bool
    {
        return match ($this) {
            self::MYSQL, self::PGSQL => true,
            self::SQLITE => false, // SQLite 通常使用文件路径
        };
    }
}
