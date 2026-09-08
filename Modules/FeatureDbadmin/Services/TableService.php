<?php

namespace Modules\FeatureDbadmin\Services;

use Modules\FeatureDbadmin\Dtos\TableStructureDto;
use Modules\FeatureDbadmin\Dtos\ColumnInfoDto;
use Modules\FeatureDbadmin\Dtos\IndexInfoDto;
use Modules\FeatureDbadmin\Models\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

/**
 * 表结构管理服务
 *
 * 获取表结构信息、列信息、索引信息等
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
        $connection = Connection::find($connectionId);

        if (!$connection) {
            return [];
        }

        $connectionName = self::getConnectionName($connectionId);

        if (!$connectionName) {
            return [];
        }

        $tables = [];

        switch ($connection->driver) {
            case 'mysql':
                $results = DB::connection($connectionName)->select('SHOW TABLES');
                $key = 'Tables_in_' . $connection->database;
                foreach ($results as $result) {
                    $tables[] = $result->$key;
                }
                break;

            case 'pgsql':
                $results = DB::connection($connectionName)->select("
                    SELECT tablename FROM pg_tables
                    WHERE schemaname = 'public'
                    ORDER BY tablename
                ");
                foreach ($results as $result) {
                    $tables[] = $result->tablename;
                }
                break;

            case 'sqlite':
                $results = DB::connection($connectionName)->select("
                    SELECT name FROM sqlite_master
                    WHERE type = 'table' AND name NOT LIKE 'sqlite_%'
                    ORDER BY name
                ");
                foreach ($results as $result) {
                    $tables[] = $result->name;
                }
                break;
        }

        return $tables;
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
        $connection = Connection::find($connectionId);

        if (!$connection) {
            return [];
        }

        $connectionName = self::getConnectionName($connectionId);

        if (!$connectionName) {
            return [];
        }

        $columns = [];

        switch ($connection->driver) {
            case 'mysql':
                $columns = self::getMySqlColumns($connectionName, $tableName);
                break;

            case 'pgsql':
                $columns = self::getPgsqlColumns($connectionName, $tableName);
                break;

            case 'sqlite':
                $columns = self::getSqliteColumns($connectionName, $tableName);
                break;
        }

        return $columns;
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
        $connection = Connection::find($connectionId);

        if (!$connection) {
            return [];
        }

        $connectionName = self::getConnectionName($connectionId);

        if (!$connectionName) {
            return [];
        }

        $indexes = [];

        switch ($connection->driver) {
            case 'mysql':
                $indexes = self::getMySqlIndexes($connectionName, $tableName);
                break;

            case 'pgsql':
                $indexes = self::getPgsqlIndexes($connectionName, $tableName);
                break;

            case 'sqlite':
                $indexes = self::getSqliteIndexes($connectionName, $tableName);
                break;
        }

        return $indexes;
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
        $connection = Connection::find($connectionId);

        if (!$connection) {
            return [];
        }

        $connectionName = self::getConnectionName($connectionId);

        if (!$connectionName) {
            return [];
        }

        $foreignKeys = [];

        switch ($connection->driver) {
            case 'mysql':
                $foreignKeys = self::getMySqlForeignKeys($connectionName, $tableName, $connection->database);
                break;

            case 'pgsql':
                $foreignKeys = self::getPgsqlForeignKeys($connectionName, $tableName);
                break;

            case 'sqlite':
                $foreignKeys = self::getSqliteForeignKeys($connectionName, $tableName);
                break;
        }

        return $foreignKeys;
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
        $connectionName = self::getConnectionName($connectionId);

        if (!$connectionName) {
            return 0;
        }

        $result = DB::connection($connectionName)->selectOne("SELECT COUNT(*) as count FROM `{$tableName}`");

        return $result->count ?? 0;
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
        $connection = Connection::find($connectionId);

        if (!$connection) {
            return '0 B';
        }

        $connectionName = self::getConnectionName($connectionId);

        if (!$connectionName) {
            return '0 B';
        }

        $size = 0;

        switch ($connection->driver) {
            case 'mysql':
                $result = DB::connection($connectionName)->selectOne("
                    SELECT ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
                    FROM information_schema.TABLES
                    WHERE table_schema = DATABASE()
                    AND table_name = ?
                ", [$tableName]);
                $size = ($result->size_mb ?? 0) * 1024 * 1024;
                break;

            case 'pgsql':
                $result = DB::connection($connectionName)->selectOne("
                    SELECT pg_total_relation_size('" . $tableName . "') as size
                ");
                $size = $result->size ?? 0;
                break;

            case 'sqlite':
                $result = DB::connection($connectionName)->selectOne("
                    SELECT SUM(pgsize) as size FROM dbstat WHERE name = ?
                ", [$tableName]);
                $size = ($result->size ?? 0) * 1024;
                break;
        }

        return self::formatBytes($size);
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

        $connectionName = self::getConnectionName($connectionId);

        if (!$connectionName) {
            return new TableStructureDto($tableName);
        }

        // 获取基本信息
        $engine = null;
        $charset = null;
        $collation = null;
        $comment = null;
        $createTime = null;
        $updateTime = null;
        $createSql = null;

        if ($connection->driver === 'mysql') {
            $result = DB::connection($connectionName)->selectOne("
                SELECT ENGINE, TABLE_COLLATION, TABLE_COMMENT, CREATE_TIME, UPDATE_TIME
                FROM information_schema.TABLES
                WHERE table_schema = DATABASE()
                AND table_name = ?
            ", [$tableName]);

            if ($result) {
                $engine = $result->ENGINE;
                $collation = $result->TABLE_COLLATION;
                $charset = $collation ? explode('_', $collation)[0] : null;
                $comment = $result->TABLE_COMMENT;
                $createTime = $result->CREATE_TIME;
                $updateTime = $result->UPDATE_TIME;
            }

            // 获取 CREATE TABLE SQL
            $showResult = DB::connection($connectionName)->selectOne("SHOW CREATE TABLE `{$tableName}`");
            $createSql = $showResult->{'Create Table'} ?? null;
        }

        if ($connection->driver === 'pgsql') {
            $result = DB::connection($connectionName)->selectOne("
                SELECT pg_catalog.obj_description('" . $tableName . "'::regclass, 'pg_class') as comment
            ");
            $comment = $result->comment ?? null;
        }

        // 获取行数
        $rowCount = self::getTableRowCount($connectionId, $tableName);

        // 获取表大小
        $tableSize = self::getTableSize($connectionId, $tableName);

        // 获取列信息
        $columns = self::getColumns($connectionId, $tableName);

        // 获取索引信息
        $indexes = self::getIndexes($connectionId, $tableName);

        // 获取外键信息
        $foreignKeys = self::getForeignKeys($connectionId, $tableName);

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

    // ==================== 私有辅助方法 ====================

    /**
     * 获取连接名称
     *
     * @param int $connectionId 连接ID
     * @return string|null
     */
    private static function getConnectionName(int $connectionId): ?string
    {
        $connection = Connection::find($connectionId);

        if (!$connection) {
            return null;
        }

        $connectionName = $connection->getDynamicConnectionName();
        $connection->registerDynamicConnection();

        return $connectionName;
    }

    /**
     * 格式化字节大小
     *
     * @param float $bytes 字节数
     * @return string
     */
    private static function formatBytes(float $bytes): string
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

    // ==================== MySQL 特定方法 ====================

    /**
     * 获取 MySQL 列信息
     *
     * @param string $connectionName 连接名称
     * @param string $tableName 表名
     * @return array<ColumnInfoDto>
     */
    private static function getMySqlColumns(string $connectionName, string $tableName): array
    {
        $results = DB::connection($connectionName)->select("
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
     * 获取 MySQL 索引信息
     *
     * @param string $connectionName 连接名称
     * @param string $tableName 表名
     * @return array<IndexInfoDto>
     */
    private static function getMySqlIndexes(string $connectionName, string $tableName): array
    {
        $results = DB::connection($connectionName)->select("SHOW INDEX FROM `{$tableName}`");

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
     * 获取 MySQL 外键信息
     *
     * @param string $connectionName 连接名称
     * @param string $tableName 表名
     * @param string $database 数据库名
     * @return array
     */
    private static function getMySqlForeignKeys(string $connectionName, string $tableName, string $database): array
    {
        $results = DB::connection($connectionName)->select("
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
        ", [$database, $tableName]);

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

    // ==================== PostgreSQL 特定方法 ====================

    /**
     * 获取 PostgreSQL 列信息
     *
     * @param string $connectionName 连接名称
     * @param string $tableName 表名
     * @return array<ColumnInfoDto>
     */
    private static function getPgsqlColumns(string $connectionName, string $tableName): array
    {
        $results = DB::connection($connectionName)->select("
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
     * 获取 PostgreSQL 索引信息
     *
     * @param string $connectionName 连接名称
     * @param string $tableName 表名
     * @return array<IndexInfoDto>
     */
    private static function getPgsqlIndexes(string $connectionName, string $tableName): array
    {
        $results = DB::connection($connectionName)->select("
            SELECT
                i.relname as index_name,
                am.amname as index_type,
                pg_get_indexdef(i.oid) as index_def,
                array_agg(a.attname ORDER BY x.ord) as columns
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

            $indexes[] = new IndexInfoDto(
                name: $row->index_name,
                type: $type,
                columns: $row->columns,
                algorithm: $row->index_type,
                comment: null,
                cardinality: null,
            );
        }

        return $indexes;
    }

    /**
     * 获取 PostgreSQL 外键信息
     *
     * @param string $connectionName 连接名称
     * @param string $tableName 表名
     * @return array
     */
    private static function getPgsqlForeignKeys(string $connectionName, string $tableName): array
    {
        $results = DB::connection($connectionName)->select("
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

    // ==================== SQLite 特定方法 ====================

    /**
     * 获取 SQLite 列信息
     *
     * @param string $connectionName 连接名称
     * @param string $tableName 表名
     * @return array<ColumnInfoDto>
     */
    private static function getSqliteColumns(string $connectionName, string $tableName): array
    {
        $results = DB::connection($connectionName)->select("PRAGMA table_info({$tableName})");

        $columns = [];

        foreach ($results as $row) {
            $columns[] = new ColumnInfoDto(
                name: $row->name,
                type: $row->type,
                collation: null,
                nullable: $row->notnull ? 'NO' : 'YES',
                default: $row->dflt_value,
                extra: $row->pk ? 'primary_key' : null,
                comment: null,
                maxLength: null,
                precision: null,
                scale: null,
                isPrimaryKey: (bool) $row->pk,
                isAutoIncrement: false,
                isUnique: false,
                isIndex: false,
            );
        }

        return $columns;
    }

    /**
     * 获取 SQLite 索引信息
     *
     * @param string $connectionName 连接名称
     * @param string $tableName 表名
     * @return array<IndexInfoDto>
     */
    private static function getSqliteIndexes(string $connectionName, string $tableName): array
    {
        $results = DB::connection($connectionName)->select("PRAGMA index_list({$tableName})");

        $indexes = [];

        foreach ($results as $row) {
            $indexInfo = DB::connection($connectionName)->select("PRAGMA index_info({$row->name})");
            $columns = [];
            foreach ($indexInfo as $info) {
                $columns[] = $info->name;
            }

            $type = $row->unique ? 'UNIQUE' : 'INDEX';
            if ($row->origin === 'pk') {
                $type = 'PRIMARY';
            }

            $indexes[] = new IndexInfoDto(
                name: $row->name,
                type: $type,
                columns: $columns,
                algorithm: null,
                comment: null,
                cardinality: null,
            );
        }

        return $indexes;
    }

    /**
     * 获取 SQLite 外键信息
     *
     * @param string $connectionName 连接名称
     * @param string $tableName 表名
     * @return array
     */
    private static function getSqliteForeignKeys(string $connectionName, string $tableName): array
    {
        $results = DB::connection($connectionName)->select("PRAGMA foreign_key_list({$tableName})");

        $foreignKeys = [];

        foreach ($results as $row) {
            $foreignKeys[] = [
                'id' => $row->id,
                'seq' => $row->seq,
                'table' => $row->table,
                'from' => $row->from,
                'to' => $row->to,
                'on_update' => $row->on_update,
                'on_delete' => $row->on_delete,
                'match' => $row->match,
            ];
        }

        return $foreignKeys;
    }
}
