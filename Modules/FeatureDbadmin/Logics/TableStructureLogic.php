<?php

namespace Modules\FeatureDbadmin\Logics;

use Modules\FeatureDbadmin\Dtos\ColumnInfoDto;
use Modules\FeatureDbadmin\Dtos\IndexInfoDto;

/**
 * 表结构解析逻辑
 *
 * 提供解析表结构信息、转换格式等功能
 */
class TableStructureLogic
{
    /**
     * 解析建表 SQL
     *
     * 解析 CREATE TABLE 语句，提取表名、引擎、字符集、注释
     *
     * @param string $sql CREATE TABLE 语句
     * @return array 包含 table, engine, charset, collation, comment 的关联数组
     */
    public static function parseCreateTableSql(string $sql): array
    {
        $result = [
            'table' => null,
            'engine' => null,
            'charset' => null,
            'collation' => null,
            'comment' => null,
        ];

        // 提取表名
        if (preg_match('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?[`"]?([^`"\s(]+)[`"]?/i', $sql, $matches)) {
            $result['table'] = $matches[1];
        }

        // 提取引擎
        if (preg_match('/ENGINE\s*=\s*(\w+)/i', $sql, $matches)) {
            $result['engine'] = $matches[1];
        }

        // 提取字符集
        if (preg_match('/CHARSET\s*=\s*(\w+)/i', $sql, $matches)) {
            $result['charset'] = $matches[1];
        } elseif (preg_match('/CHARACTER\s+SET\s+(\w+)/i', $sql, $matches)) {
            $result['charset'] = $matches[1];
        }

        // 提取排序规则
        if (preg_match('/COLLATE\s*=\s*(\w+)/i', $sql, $matches)) {
            $result['collation'] = $matches[1];
        } elseif (preg_match('/COLLATE\s+(\w+)/i', $sql, $matches)) {
            $result['collation'] = $matches[1];
        }

        // 提取表注释
        if (preg_match('/COMMENT\s*=\s*[\'"]([^\'"]*)[\'"]/i', $sql, $matches)) {
            $result['comment'] = $matches[1];
        }

        return $result;
    }

    /**
     * 解析列信息
     *
     * 根据 MySQL/PostgreSQL/SQLite 的不同字段映射转换为 ColumnInfoDto
     *
     * @param array $rawColumn 从 information_schema 或 PRAGMA 获取的原始列数据
     * @return ColumnInfoDto 列信息 DTO
     */
    public static function parseColumnInfo(array $rawColumn): ColumnInfoDto
    {
        // MySQL 格式 (DESCRIBE / SHOW COLUMNS)
        if (isset($rawColumn['Field'])) {
            $type = $rawColumn['Type'] ?? '';
            $nullable = ($rawColumn['Null'] ?? '') === 'YES' ? 'YES' : 'NO';
            $default = $rawColumn['Default'];
            $extra = $rawColumn['Extra'] ?? '';

            return new ColumnInfoDto(
                name: $rawColumn['Field'],
                type: $type,
                collation: $rawColumn['Collation'] ?? null,
                nullable: $nullable,
                default: $default,
                extra: $extra !== '' ? $extra : null,
                comment: $rawColumn['Comment'] ?? null,
                maxLength: self::extractMaxLength($type),
                precision: self::extractPrecision($type),
                scale: self::extractScale($type),
                isPrimaryKey: str_contains($extra, 'auto_increment') || str_contains($extra, 'pri'),
                isAutoIncrement: str_contains($extra, 'auto_increment'),
                isUnique: false,
                isIndex: str_contains($extra, 'pri'),
            );
        }

        // PostgreSQL 格式
        if (isset($rawColumn['column_name'])) {
            $type = $rawColumn['data_type'] ?? '';
            $nullable = ($rawColumn['is_nullable'] ?? '') === 'YES' ? 'YES' : 'NO';

            return new ColumnInfoDto(
                name: $rawColumn['column_name'],
                type: $type,
                collation: $rawColumn['collation_name'] ?? null,
                nullable: $nullable,
                default: $rawColumn['column_default'] ?? null,
                extra: null,
                comment: $rawColumn['description'] ?? null,
                maxLength: $rawColumn['character_maximum_length'] ?? null,
                precision: $rawColumn['numeric_precision'] ?? null,
                scale: $rawColumn['numeric_scale'] ?? null,
                isPrimaryKey: ($rawColumn['is_identity'] ?? 'NO') === 'YES',
                isAutoIncrement: ($rawColumn['is_identity'] ?? 'NO') === 'YES',
                isUnique: false,
                isIndex: false,
            );
        }

        // SQLite 格式 (PRAGMA table_info)
        if (isset($rawColumn['name'])) {
            $type = $rawColumn['type'] ?? 'TEXT';
            $notNull = ($rawColumn['notnull'] ?? 0) === 1;

            return new ColumnInfoDto(
                name: $rawColumn['name'],
                type: $type,
                collation: null,
                nullable: $notNull ? 'NO' : 'YES',
                default: $rawColumn['dflt_value'] ?? null,
                extra: ($rawColumn['pk'] ?? 0) === 1 ? 'PRIMARY KEY' : null,
                comment: null,
                maxLength: self::extractMaxLength($type),
                precision: self::extractPrecision($type),
                scale: self::extractScale($type),
                isPrimaryKey: ($rawColumn['pk'] ?? 0) === 1,
                isAutoIncrement: ($rawColumn['pk'] ?? 0) === 1 && strtoupper($type) === 'INTEGER',
                isUnique: false,
                isIndex: ($rawColumn['pk'] ?? 0) === 1,
            );
        }

        // 默认返回
        return new ColumnInfoDto(
            name: $rawColumn['name'] ?? 'unknown',
            type: $rawColumn['type'] ?? 'unknown',
        );
    }

    /**
     * 解析索引信息
     *
     * 从 SHOW INDEX 或系统表获取的原始索引数据转换为 IndexInfoDto
     *
     * @param array $rawIndex 原始索引数据
     * @return IndexInfoDto 索引信息 DTO
     */
    public static function parseIndexInfo(array $rawIndex): IndexInfoDto
    {
        // MySQL SHOW INDEX 格式
        if (isset($rawIndex['Key_name'])) {
            $type = 'INDEX';
            if ($rawIndex['Key_name'] === 'PRIMARY') {
                $type = 'PRIMARY';
            } elseif ($rawIndex['Non_unique'] == 0) {
                $type = 'UNIQUE';
            } elseif ($rawIndex['Index_type'] === 'FULLTEXT') {
                $type = 'FULLTEXT';
            }

            return new IndexInfoDto(
                name: $rawIndex['Key_name'],
                type: $type,
                columns: [$rawIndex['Column_name']],
                algorithm: $rawIndex['Index_type'] ?? null,
                comment: $rawIndex['Comment'] ?? null,
                cardinality: $rawIndex['Cardinality'] ?? null,
            );
        }

        // PostgreSQL pg_indexes 格式
        if (isset($rawIndex['indexname'])) {
            return new IndexInfoDto(
                name: $rawIndex['indexname'],
                type: $rawIndex['indexdef'] && str_contains($rawIndex['indexdef'], 'UNIQUE') ? 'UNIQUE' : 'INDEX',
                columns: $rawIndex['columns'] ?? [],
                algorithm: null,
                comment: null,
                cardinality: null,
            );
        }

        // SQLite PRAGMA index_info 格式
        if (isset($rawIndex['name'])) {
            $type = ($rawIndex['unique'] ?? 0) === 1 ? 'UNIQUE' : 'INDEX';
            if (str_starts_with(strtolower($rawIndex['name']), 'sqlite_autoindex')) {
                $type = 'PRIMARY';
            }

            return new IndexInfoDto(
                name: $rawIndex['name'],
                type: $type,
                columns: $rawIndex['columns'] ?? [],
                algorithm: null,
                comment: null,
                cardinality: null,
            );
        }

        // 默认返回
        return new IndexInfoDto(
            name: $rawIndex['name'] ?? 'unknown',
            type: 'INDEX',
            columns: [],
        );
    }

    /**
     * 格式化表大小
     *
     * 将字节数转换为人类可读格式（如 1.5 MB）
     *
     * @param int $bytes 字节数
     * @return string 格式化后的大小字符串
     */
    public static function formatTableSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $unitIndex = 0;

        $size = $bytes;
        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        // 小于 10 时保留两位小数，否则保留一位
        $decimals = $size < 10 ? 2 : 1;

        return round($size, $decimals) . ' ' . $units[$unitIndex];
    }

    /**
     * 从类型字符串提取最大长度
     *
     * @param string $type 类型字符串（如 varchar(255)）
     * @return int|null 最大长度
     */
    private static function extractMaxLength(string $type): ?int
    {
        if (preg_match('/\((\d+)\)/', $type, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    /**
     * 从类型字符串提取精度
     *
     * @param string $type 类型字符串（如 decimal(10,2)）
     * @return int|null 精度
     */
    private static function extractPrecision(string $type): ?int
    {
        if (preg_match('/\((\d+),\s*\d+\)/', $type, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    /**
     * 从类型字符串提取小数位
     *
     * @param string $type 类型字符串（如 decimal(10,2)）
     * @return int|null 小数位
     */
    private static function extractScale(string $type): ?int
    {
        if (preg_match('/\(\d+,\s*(\d+)\)/', $type, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }
}
