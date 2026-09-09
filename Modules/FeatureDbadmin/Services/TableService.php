<?php

namespace Modules\FeatureDbadmin\Services;

use Modules\FeatureDbadmin\Dtos\TableStructureDto;
use Modules\FeatureDbadmin\Dtos\ColumnInfoDto;
use Modules\FeatureDbadmin\Dtos\IndexInfoDto;
use Modules\FeatureDbadmin\Models\Connection;
use Modules\FeatureDbadmin\Services\Drivers\DriverFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

/**
 * 表结构管理服务
 *
 * 使用驱动模式，统一接口操作不同数据库
 * 所有方法均为静态方法
 */
class TableService
{
    /**
     * 获取所有表列表
     *
     * @param int $connectionId 连接ID
     * @return array
     */
    public static function getAllTables(int $connectionId): array
    {
        $driver = DriverFactory::createFromId($connectionId);
        return $driver->getAllTables();
    }

    /**
     * 获取列信息
     *
     * @param int $connectionId 连接ID
     * @param string $tableName 表名
     * @return array<ColumnInfoDto>
     */
    public static function getColumns(int $connectionId, string $tableName): array
    {
        $driver = DriverFactory::createFromId($connectionId);
        return $driver->getColumns($tableName);
    }

    /**
     * 获取索引信息
     *
     * @param int $connectionId 连接ID
     * @param string $tableName 表名
     * @return array<IndexInfoDto>
     */
    public static function getIndexes(int $connectionId, string $tableName): array
    {
        $driver = DriverFactory::createFromId($connectionId);
        return $driver->getIndexes($tableName);
    }

    /**
     * 获取外键信息
     *
     * @param int $connectionId 连接ID
     * @param string $tableName 表名
     * @return array
     */
    public static function getForeignKeys(int $connectionId, string $tableName): array
    {
        $driver = DriverFactory::createFromId($connectionId);
        return $driver->getForeignKeys($tableName);
    }

    /**
     * 获取表行数
     *
     * @param int $connectionId 连接ID
     * @param string $tableName 表名
     * @return int
     */
    public static function getTableRowCount(int $connectionId, string $tableName): int
    {
        $driver = DriverFactory::createFromId($connectionId);
        return $driver->getTableRowCount($tableName);
    }

    /**
     * 获取表大小
     *
     * @param int $connectionId 连接ID
     * @param string $tableName 表名
     * @return string
     */
    public static function getTableSize(int $connectionId, string $tableName): string
    {
        $driver = DriverFactory::createFromId($connectionId);
        return $driver->getTableSize($tableName);
    }

    /**
     * 获取完整表结构
     *
     * @param int $connectionId 连接ID
     * @param string $tableName 表名
     * @return TableStructureDto
     */
    public static function getTableStructure(int $connectionId, string $tableName): TableStructureDto
    {
        $connection = Connection::find($connectionId);

        if (!$connection) {
            return new TableStructureDto($tableName);
        }

        $driver = DriverFactory::create($connection);

        // 获取基本信息
        $engine = $driver->getTableEngine($tableName);
        $charset = null;
        $collation = null;
        $comment = $driver->getTableComment($tableName);
        $createTime = null;
        $updateTime = null;
        $createSql = $driver->getCreateSql($tableName);

        // MySQL 特有字段
        if ($connection->driver === 'mysql') {
            $result = DB::connection($connection->getDynamicConnectionName())->selectOne("
                SELECT TABLE_COLLATION, CREATE_TIME, UPDATE_TIME
                FROM information_schema.TABLES
                WHERE table_schema = DATABASE()
                AND table_name = ?
            ", [$tableName]);

            if ($result) {
                $collation = $result->TABLE_COLLATION;
                $charset = $collation ? explode('_', $collation)[0] : null;
                $createTime = $result->CREATE_TIME;
                $updateTime = $result->UPDATE_TIME;
            }
        }

        // 获取行数
        $rowCount = $driver->getTableRowCount($tableName);

        // 获取表大小
        $tableSize = $driver->getTableSize($tableName);

        // 获取列信息
        $columns = $driver->getColumns($tableName);

        // 获取索引信息
        $indexes = $driver->getIndexes($tableName);

        // 获取外键信息
        $foreignKeys = $driver->getForeignKeys($tableName);

        return new TableStructureDto(
            tableName: $tableName,
            engine: $engine,
            charset: $charset,
            collation: $collation,
            rowCount: $rowCount,
            tableSize: $tableSize,
            comment: $comment,
            createTime: $createTime,
            updateTime: $updateTime,
            columns: $columns,
            indexes: $indexes,
            foreignKeys: $foreignKeys,
            createSql: $createSql,
        );
    }
}