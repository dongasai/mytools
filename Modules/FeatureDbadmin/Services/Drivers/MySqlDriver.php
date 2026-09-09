<?php

namespace Modules\FeatureDbadmin\Services\Drivers;

use Illuminate\Support\Facades\DB;
use Modules\FeatureDbadmin\Dtos\ColumnInfoDto;
use Modules\FeatureDbadmin\Dtos\IndexInfoDto;

/**
 * MySQL 数据库驱动
 */
class MySqlDriver implements DatabaseDriverInterface
{
    private string $connectionName;
    private string $database;

    public function __construct(string $connectionName)
    {
        $this->connectionName = $connectionName;
        $this->database = DB::connection($this->connectionName)->getDatabaseName();
    }

    /**
     * 获取所有数据库列表
     */
    public function getDatabases(): array
    {
        $results = DB::connection($this->connectionName)->select("SHOW DATABASES");
        $databases = [];
        foreach ($results as $result) {
            $databases[] = $result->Database;
        }
        return $databases;
    }

    /**
     * 获取所有模式（MySQL 中模式 = 数据库）
     */
    public function getSchemas(string $database): array
    {
        // MySQL 中 schema 概念简化，返回一个默认模式
        return [$database];
    }

    /**
     * 获取所有表列表
     */
    public function getAllTables(string $database = '', string $schema = ''): array
    {
        // 如果指定了数据库，切换到该数据库
        if ($database) {
            DB::connection($this->connectionName)->statement("USE `{$database}`");
        }

        $results = DB::connection($this->connectionName)->select('SHOW TABLES');
        $key = 'Tables_in_' . ($database ?: $this->database);

        $tables = [];
        foreach ($results as $result) {
            $tables[] = $result->$key;
        }

        return $tables;
    }

    /**
     * 获取列信息
     */
    public function getColumns(string $tableName): array
    {
        $results = DB::connection($this->connectionName)->select("
            SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_SET_NAME, COLLATION_NAME,
                   IS_NULLABLE, COLUMN_DEFAULT, EXTRA, COLUMN_COMMENT,
                   CHARACTER_MAXIMUM_LENGTH, NUMERIC_PRECISION, NUMERIC_SCALE,
                   COLUMN_KEY
            FROM information_schema.COLUMNS
            WHERE table_schema = DATABASE()
            AND table_name = ?
            ORDER BY ORDINAL_POSITION
        ", [$tableName]);

        $columns = [];
        foreach ($results as $row) {
            $columns[] = new ColumnInfoDto(
                name: $row->COLUMN_NAME,
                type: $row->DATA_TYPE,
                collation: $row->COLLATION_NAME,
                nullable: $row->IS_NULLABLE,
                default: $row->COLUMN_DEFAULT,
                extra: $row->EXTRA,
                comment: $row->COLUMN_COMMENT,
                maxLength: $row->CHARACTER_MAXIMUM_LENGTH,
                precision: $row->NUMERIC_PRECISION,
                scale: $row->NUMERIC_SCALE,
                isPrimaryKey: $row->COLUMN_KEY === 'PRI',
                isAutoIncrement: $row->EXTRA === 'auto_increment',
                isUnique: $row->COLUMN_KEY === 'UNI',
                isIndex: $row->COLUMN_KEY === 'MUL',
            );
        }

        return $columns;
    }

    /**
     * 获取索引信息
     */
    public function getIndexes(string $tableName): array
    {
        $results = DB::connection($this->connectionName)->select("SHOW INDEX FROM `{$tableName}`");

        $indexGroups = [];
        foreach ($results as $row) {
            $name = $row->Key_name;
            if (!isset($indexGroups[$name])) {
                $indexGroups[$name] = [
                    'type' => $row->Key_name === 'PRIMARY' ? 'PRIMARY' : ($row->Non_unique == 0 ? 'UNIQUE' : 'INDEX'),
                    'columns' => [],
                    'comment' => null,
                    'cardinality' => $row->Cardinality,
                ];
            }
            $indexGroups[$name]['columns'][] = $row->Column_name;
        }

        $indexes = [];
        foreach ($indexGroups as $name => $info) {
            $indexes[] = new IndexInfoDto(
                name: $name,
                type: $info['type'],
                columns: $info['columns'],
                comment: $info['comment'],
                cardinality: $info['cardinality'],
            );
        }

        return $indexes;
    }

    /**
     * 获取外键信息
     */
    public function getForeignKeys(string $tableName): array
    {
        $results = DB::connection($this->connectionName)->select("
            SELECT
                kcu.CONSTRAINT_NAME,
                kcu.COLUMN_NAME,
                kcu.REFERENCED_TABLE_NAME,
                kcu.REFERENCED_COLUMN_NAME,
                rc.UPDATE_RULE,
                rc.DELETE_RULE
            FROM information_schema.KEY_COLUMN_USAGE kcu
            JOIN information_schema.REFERENTIAL_CONSTRAINTS rc
                ON kcu.CONSTRAINT_NAME = rc.CONSTRAINT_NAME
                AND kcu.TABLE_SCHEMA = rc.CONSTRAINT_SCHEMA
            WHERE kcu.TABLE_SCHEMA = ?
            AND kcu.TABLE_NAME = ?
            AND kcu.REFERENCED_TABLE_NAME IS NOT NULL
        ", [$this->database, $tableName]);

        $foreignKeys = [];
        foreach ($results as $row) {
            $foreignKeys[] = [
                'name' => $row->CONSTRAINT_NAME,
                'column' => $row->COLUMN_NAME,
                'referenced_table' => $row->REFERENCED_TABLE_NAME,
                'referenced_column' => $row->REFERENCED_COLUMN_NAME,
                'update_rule' => $row->UPDATE_RULE,
                'delete_rule' => $row->DELETE_RULE,
            ];
        }

        return $foreignKeys;
    }

    /**
     * 获取表行数
     */
    public function getTableRowCount(string $tableName): int
    {
        return DB::connection($this->connectionName)->table($tableName)->count();
    }

    /**
     * 获取表大小
     */
    public function getTableSize(string $tableName): string
    {
        $result = DB::connection($this->connectionName)->selectOne("
            SELECT ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
            FROM information_schema.TABLES
            WHERE table_schema = DATABASE()
            AND table_name = ?
        ", [$tableName]);

        $size = ($result->size_mb ?? 0) * 1024 * 1024;
        return $this->formatBytes($size);
    }

    /**
     * 获取表引擎
     */
    public function getTableEngine(string $tableName): ?string
    {
        $result = DB::connection($this->connectionName)->selectOne("
            SELECT ENGINE
            FROM information_schema.TABLES
            WHERE table_schema = DATABASE()
            AND table_name = ?
        ", [$tableName]);

        return $result->ENGINE ?? null;
    }

    /**
     * 获取表注释
     */
    public function getTableComment(string $tableName): ?string
    {
        $result = DB::connection($this->connectionName)->selectOne("
            SELECT TABLE_COMMENT
            FROM information_schema.TABLES
            WHERE table_schema = DATABASE()
            AND table_name = ?
        ", [$tableName]);

        return $result->TABLE_COMMENT ?? null;
    }

    /**
     * 获取创建表 SQL
     */
    public function getCreateSql(string $tableName): ?string
    {
        $result = DB::connection($this->connectionName)->selectOne("SHOW CREATE TABLE `{$tableName}`");
        return $result->{'Create Table'} ?? null;
    }

    /**
     * 创建测试表
     */
    public function createTestTable(string $tableName): void
    {
        DB::connection($this->connectionName)->statement("
            CREATE TABLE IF NOT EXISTS `{$tableName}` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '自增主键',
                `name` VARCHAR(100) NOT NULL COMMENT '姓名',
                `email` VARCHAR(255) NOT NULL UNIQUE COMMENT '邮箱',
                `age` TINYINT UNSIGNED DEFAULT 0 COMMENT '年龄',
                `score` DECIMAL(5,2) DEFAULT 0.00 COMMENT '分数',
                `is_active` BOOLEAN DEFAULT TRUE COMMENT '是否激活',
                `bio` TEXT COMMENT '个人简介',
                `settings` JSON COMMENT '设置(JSON)',
                `avatar` VARCHAR(255) COMMENT '头像URL',
                `birth_date` DATE COMMENT '出生日期',
                `login_time` DATETIME COMMENT '登录时间',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='FeatureDbadmin 测试表'
        ");

        // 插入测试数据
        DB::connection($this->connectionName)->table($tableName)->insert([
            [
                'name' => '张三',
                'email' => 'zhangsan@example.com',
                'age' => 25,
                'score' => 88.50,
                'is_active' => true,
                'bio' => '这是一段个人简介，用于测试 TEXT 字段类型。',
                'settings' => json_encode(['theme' => 'dark', 'language' => 'zh-CN']),
                'avatar' => 'https://example.com/avatar1.jpg',
                'birth_date' => '2000-01-15',
                'login_time' => '2026-09-09 10:30:00',
            ],
            [
                'name' => '李四',
                'email' => 'lisi@example.com',
                'age' => 30,
                'score' => 92.75,
                'is_active' => true,
                'bio' => '另一个用户的个人简介。',
                'settings' => json_encode(['theme' => 'light', 'language' => 'en-US']),
                'avatar' => 'https://example.com/avatar2.jpg',
                'birth_date' => '1995-08-20',
                'login_time' => '2026-09-08 15:45:00',
            ],
        ]);
    }

    /**
     * 格式化字节大小
     */
    private function formatBytes(float $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $unitIndex = 0;
        $size = $bytes;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }
}