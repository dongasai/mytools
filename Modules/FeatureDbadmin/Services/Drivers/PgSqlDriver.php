<?php

namespace Modules\FeatureDbadmin\Services\Drivers;

use Illuminate\Support\Facades\DB;
use Modules\FeatureDbadmin\Dtos\ColumnInfoDto;
use Modules\FeatureDbadmin\Dtos\IndexInfoDto;

/**
 * PostgreSQL 数据库驱动
 */
class PgSqlDriver implements DatabaseDriverInterface
{
    private string $connectionName;

    public function __construct(string $connectionName)
    {
        $this->connectionName = $connectionName;
    }

    /**
     * 获取所有数据库列表
     */
    public function getDatabases(): array
    {
        $results = DB::connection($this->connectionName)->select("
            SELECT datname FROM pg_database
            WHERE datistemplate = false
            ORDER BY datname
        ");

        $databases = [];
        foreach ($results as $result) {
            $databases[] = $result->datname;
        }

        return $databases;
    }

    /**
     * 获取指定数据库的所有模式
     */
    public function getSchemas(string $database): array
    {
        $results = DB::connection($this->connectionName)->select("
            SELECT schema_name
            FROM information_schema.schemata
            WHERE schema_name NOT IN ('information_schema', 'pg_catalog', 'pg_toast')
            ORDER BY schema_name
        ");

        $schemas = [];
        foreach ($results as $result) {
            $schemas[] = $result->schema_name;
        }

        return $schemas;
    }

    /**
     * 获取所有表列表
     */
    public function getAllTables(string $database = '', string $schema = 'public'): array
    {
        $schema = $schema ?: 'public';

        $results = DB::connection($this->connectionName)->select("
            SELECT tablename FROM pg_tables
            WHERE schemaname = ?
            ORDER BY tablename
        ", [$schema]);

        $tables = [];
        foreach ($results as $result) {
            $tables[] = $result->tablename;
        }

        return $tables;
    }

    /**
     * 获取列信息
     */
    public function getColumns(string $tableName): array
    {
        $results = DB::connection($this->connectionName)->select("
            SELECT
                c.column_name,
                c.data_type,
                c.is_nullable,
                c.column_default,
                c.character_maximum_length,
                c.numeric_precision,
                c.numeric_scale,
                pg_catalog.col_description(pgc.oid, c.ordinal_position) as column_comment,
                EXISTS (
                    SELECT 1 FROM information_schema.table_constraints tc
                    JOIN information_schema.constraint_column_usage ccu
                        ON tc.constraint_name = ccu.constraint_name
                    WHERE tc.table_name = c.table_name
                    AND tc.constraint_type = 'PRIMARY KEY'
                    AND ccu.column_name = c.column_name
                ) as is_primary_key
            FROM information_schema.columns c
            JOIN pg_catalog.pg_class pgc ON pgc.relname = c.table_name
            WHERE c.table_name = ?
            ORDER BY c.ordinal_position
        ", [$tableName]);

        $columns = [];
        foreach ($results as $row) {
            $columns[] = new ColumnInfoDto(
                name: $row->column_name,
                type: $row->data_type,
                collation: null,
                nullable: $row->is_nullable,
                default: $row->column_default,
                extra: null,
                comment: $row->column_comment,
                maxLength: $row->character_maximum_length,
                precision: $row->numeric_precision,
                scale: $row->numeric_scale,
                isPrimaryKey: (bool) $row->is_primary_key,
                isAutoIncrement: str_contains($row->column_default ?? '', 'nextval'),
                isUnique: false,
                isIndex: false,
            );
        }

        return $columns;
    }

    /**
     * 获取索引信息
     */
    public function getIndexes(string $tableName): array
    {
        $results = DB::connection($this->connectionName)->select("
            SELECT
                i.relname as index_name,
                am.amname as index_type,
                pg_get_indexdef(i.oid) as index_def,
                array_to_string(array_agg(a.attname ORDER BY x.ord), ',') as columns
            FROM pg_index ix
            JOIN pg_class i ON i.oid = ix.indexrelid
            JOIN pg_class t ON t.oid = ix.indrelid
            JOIN pg_am am ON am.oid = i.relam
            CROSS JOIN LATERAL unnest(ix.indkey) WITH ORDINALITY AS x(attnum, ord)
            LEFT JOIN pg_attribute a ON a.attrelid = t.oid AND a.attnum = x.attnum
            WHERE t.relname = ?
            GROUP BY i.relname, am.amname, i.oid
        ", [$tableName]);

        $indexes = [];
        foreach ($results as $row) {
            $type = 'INDEX';
            if (str_starts_with($row->index_def, 'CREATE UNIQUE')) {
                $type = 'UNIQUE';
            }
            if ($row->index_name . '_pkey' === $tableName || str_ends_with($row->index_name, '_pkey')) {
                $type = 'PRIMARY';
            }

            // 将逗号分隔的字符串转换为数组
            $columns = $row->columns ? explode(',', $row->columns) : [];

            $indexes[] = new IndexInfoDto(
                name: $row->index_name,
                type: $type,
                columns: $columns,
                algorithm: $row->index_type,
                comment: null,
                cardinality: null,
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
                tc.constraint_name,
                kcu.column_name,
                ccu.table_name AS foreign_table_name,
                ccu.column_name AS foreign_column_name,
                rc.update_rule,
                rc.delete_rule
            FROM information_schema.table_constraints tc
            JOIN information_schema.key_column_usage kcu
                ON tc.constraint_name = kcu.constraint_name
            JOIN information_schema.constraint_column_usage ccu
                ON ccu.constraint_name = tc.constraint_name
            JOIN information_schema.referential_constraints rc
                ON rc.constraint_name = tc.constraint_name
            WHERE tc.constraint_type = 'FOREIGN KEY'
            AND tc.table_name = ?
        ", [$tableName]);

        $foreignKeys = [];
        foreach ($results as $row) {
            $foreignKeys[] = [
                'name' => $row->constraint_name,
                'column' => $row->column_name,
                'referenced_table' => $row->foreign_table_name,
                'referenced_column' => $row->foreign_column_name,
                'update_rule' => $row->update_rule,
                'delete_rule' => $row->delete_rule,
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
            SELECT pg_total_relation_size(?) as size
        ", [$tableName]);

        $size = $result->size ?? 0;
        return $this->formatBytes($size);
    }

    /**
     * 获取表引擎
     */
    public function getTableEngine(string $tableName): ?string
    {
        return null; // PostgreSQL 不使用引擎概念
    }

    /**
     * 获取表注释
     */
    public function getTableComment(string $tableName): ?string
    {
        // 使用 to_regclass 函数安全地转换表名，避免表不存在时报错
        $result = DB::connection($this->connectionName)->selectOne("
            SELECT pg_catalog.obj_description(
                pg_catalog.to_regclass(?),
                'pg_class'
            ) as comment
        ", [$tableName]);

        return $result->comment ?? null;
    }

    /**
     * 获取创建表 SQL
     */
    public function getCreateSql(string $tableName): ?string
    {
        return null; // PostgreSQL 不提供 SHOW CREATE TABLE
    }

    /**
     * 创建测试表
     */
    public function createTestTable(string $tableName): void
    {
        DB::connection($this->connectionName)->statement("
            CREATE TABLE IF NOT EXISTS \"{$tableName}\" (
                id SERIAL PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                age SMALLINT DEFAULT 0,
                score DECIMAL(5,2) DEFAULT 0.00,
                is_active BOOLEAN DEFAULT TRUE,
                bio TEXT,
                settings JSONB,
                avatar VARCHAR(255),
                birth_date DATE,
                login_time TIMESTAMP,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // 插入测试数据
        DB::connection($this->connectionName)->table($tableName)->insert([
            [
                'name' => '张三',
                'email' => 'zhangsan@example.com',
                'age' => 25,
                'score' => 88.50,
                'is_active' => true,
                'bio' => '这是一段个人简介',
                'settings' => json_encode(['theme' => 'dark']),
                'avatar' => 'https://example.com/avatar1.jpg',
                'birth_date' => '2000-01-15',
                'login_time' => '2026-09-09 10:30:00',
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