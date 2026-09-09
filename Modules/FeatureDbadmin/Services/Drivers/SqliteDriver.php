<?php

namespace Modules\FeatureDbadmin\Services\Drivers;

use Illuminate\Support\Facades\DB;
use Modules\FeatureDbadmin\Dtos\ColumnInfoDto;
use Modules\FeatureDbadmin\Dtos\IndexInfoDto;

/**
 * SQLite 数据库驱动
 */
class SqliteDriver implements DatabaseDriverInterface
{
    private string $connectionName;

    public function __construct(string $connectionName)
    {
        $this->connectionName = $connectionName;
    }

    /**
     * 获取所有数据库列表（SQLite 只有一个数据库）
     */
    public function getDatabases(): array
    {
        // SQLite 是单文件数据库，只有一个数据库
        return ['main'];
    }

    /**
     * 获取所有模式（SQLite 无模式概念）
     */
    public function getSchemas(string $database): array
    {
        // SQLite 无 schema 概念，返回空数组
        return [];
    }

    /**
     * 获取所有表列表
     */
    public function getAllTables(string $database = '', string $schema = ''): array
    {
        $results = DB::connection($this->connectionName)->select("
            SELECT name FROM sqlite_master
            WHERE type = 'table' AND name NOT LIKE 'sqlite_%'
            ORDER BY name
        ");

        $tables = [];
        foreach ($results as $result) {
            $tables[] = $result->name;
        }

        return $tables;
    }

    /**
     * 获取列信息
     */
    public function getColumns(string $tableName): array
    {
        $results = DB::connection($this->connectionName)->select("PRAGMA table_info({$tableName})");

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
     * 获取索引信息
     */
    public function getIndexes(string $tableName): array
    {
        $results = DB::connection($this->connectionName)->select("PRAGMA index_list({$tableName})");

        $indexes = [];
        foreach ($results as $row) {
            $indexInfo = DB::connection($this->connectionName)->select("PRAGMA index_info({$row->name})");
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
     * 获取外键信息
     */
    public function getForeignKeys(string $tableName): array
    {
        $results = DB::connection($this->connectionName)->select("PRAGMA foreign_key_list({$tableName})");

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
            SELECT SUM(pgsize) as size FROM dbstat WHERE name = ?
        ", [$tableName]);

        $size = ($result->size ?? 0) * 1024;
        return $this->formatBytes($size);
    }

    /**
     * 获取表引擎
     */
    public function getTableEngine(string $tableName): ?string
    {
        return null; // SQLite 不使用引擎概念
    }

    /**
     * 获取表注释
     */
    public function getTableComment(string $tableName): ?string
    {
        return null; // SQLite 不支持表注释
    }

    /**
     * 获取创建表 SQL
     */
    public function getCreateSql(string $tableName): ?string
    {
        $result = DB::connection($this->connectionName)->selectOne("
            SELECT sql FROM sqlite_master WHERE type='table' AND name=?
        ", [$tableName]);

        return $result->sql ?? null;
    }

    /**
     * 创建测试表
     */
    public function createTestTable(string $tableName): void
    {
        DB::connection($this->connectionName)->statement("
            CREATE TABLE IF NOT EXISTS \"{$tableName}\" (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                age INTEGER DEFAULT 0,
                score REAL DEFAULT 0.00,
                is_active INTEGER DEFAULT 1,
                bio TEXT,
                avatar TEXT,
                birth_date TEXT,
                login_time TEXT,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // 插入测试数据
        DB::connection($this->connectionName)->table($tableName)->insert([
            [
                'name' => '测试用户',
                'email' => 'test@example.com',
                'age' => 20,
                'score' => 95.5,
                'is_active' => 1,
                'bio' => 'SQLite 测试数据',
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