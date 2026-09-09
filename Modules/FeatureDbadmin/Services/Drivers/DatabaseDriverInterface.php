<?php

namespace Modules\FeatureDbadmin\Services\Drivers;

use Illuminate\Support\Facades\DB;
use Modules\FeatureDbadmin\Dtos\ColumnInfoDto;
use Modules\FeatureDbadmin\Dtos\IndexInfoDto;

/**
 * 数据库驱动接口
 *
 * 定义所有数据库驱动必须实现的方法
 */
interface DatabaseDriverInterface
{
    /**
     * 获取所有数据库列表
     *
     * @return array
     */
    public function getDatabases(): array;

    /**
     * 获取指定数据库的所有模式
     *
     * @param string $database 数据库名
     * @return array
     */
    public function getSchemas(string $database): array;

    /**
     * 获取指定模式的所有表列表
     *
     * @param string $database 数据库名
     * @param string $schema 模式名
     * @return array
     */
    public function getAllTables(string $database, string $schema): array;

    /**
     * 获取列信息
     *
     * @param string $tableName 表名
     * @return array<ColumnInfoDto>
     */
    public function getColumns(string $tableName): array;

    /**
     * 获取索引信息
     *
     * @param string $tableName 表名
     * @return array<IndexInfoDto>
     */
    public function getIndexes(string $tableName): array;

    /**
     * 获取外键信息
     *
     * @param string $tableName 表名
     * @return array
     */
    public function getForeignKeys(string $tableName): array;

    /**
     * 获取表行数
     *
     * @param string $tableName 表名
     * @return int
     */
    public function getTableRowCount(string $tableName): int;

    /**
     * 获取表大小
     *
     * @param string $tableName 表名
     * @return string
     */
    public function getTableSize(string $tableName): string;

    /**
     * 获取表引擎
     *
     * @param string $tableName 表名
     * @return string|null
     */
    public function getTableEngine(string $tableName): ?string;

    /**
     * 获取表注释
     *
     * @param string $tableName 表名
     * @return string|null
     */
    public function getTableComment(string $tableName): ?string;

    /**
     * 获取创建表 SQL
     *
     * @param string $tableName 表名
     * @return string|null
     */
    public function getCreateSql(string $tableName): ?string;

    /**
     * 创建测试表
     *
     * @param string $tableName 表名
     * @return void
     */
    public function createTestTable(string $tableName): void;
}