<?php

namespace Modules\FeatureDbadmin\Enums;

/**
 * 查询类型枚举
 *
 * 标识 SQL 查询类型，区分不同的数据操作类别
 */
enum QueryType: string
{
    /** 查询语句 - SELECT 操作 */
    case SELECT = 'SELECT';

    /** 插入语句 - INSERT 操作 */
    case INSERT = 'INSERT';

    /** 更新语句 - UPDATE 操作 */
    case UPDATE = 'UPDATE';

    /** 删除语句 - DELETE 操作 */
    case DELETE = 'DELETE';

    /** 数据定义语句 - CREATE/ALTER/DROP 等 */
    case DDL = 'DDL';

    /**
     * 获取中文标签
     *
     * @return string 查询类型的中文描述
     */
    public function label(): string
    {
        return match ($this) {
            self::SELECT => '查询',
            self::INSERT => '插入',
            self::UPDATE => '更新',
            self::DELETE => '删除',
            self::DDL => '数据定义',
        };
    }

    /**
     * 是否为写操作
     *
     * 写操作包括 INSERT、UPDATE、DELETE
     * SELECT 和 DDL 不属于写操作
     *
     * @return bool 是否为写操作类型
     */
    public function isWrite(): bool
    {
        return match ($this) {
            self::INSERT, self::UPDATE, self::DELETE => true,
            default => false,
        };
    }
}
