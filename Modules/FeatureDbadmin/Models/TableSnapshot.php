<?php

namespace Modules\FeatureDbadmin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 表结构快照模型
 *
 * @property int $id 快照ID
 * @property string $connection_name 连接名称
 * @property string $table_name 表名
 * @property string|null $schema_name Schema名称
 * @property array $table_structure 表结构信息
 * @property int $column_count 字段数量
 * @property int $index_count 索引数量
 * @property int $foreign_key_count 外键数量
 * @property int|null $row_count 数据行数
 * @property int|null $table_size 表大小(字节)
 * @property \Carbon\Carbon $snapshot_at 快照时间
 * @property \Carbon\Carbon|null $created_at 创建时间
 * @property \Carbon\Carbon|null $updated_at 更新时间
 */
class TableSnapshot extends Model
{
    use HasFactory;

    /**
     * 表名
     */
    protected $table = 'feature_dbadmin_table_snapshots';

    /**
     * 可填充字段
     */
    protected $fillable = [
        'connection_name',
        'table_name',
        'schema_name',
        'table_structure',
        'column_count',
        'index_count',
        'foreign_key_count',
        'row_count',
        'table_size',
        'snapshot_at',
    ];

    /**
     * 字段类型转换
     */
    protected $casts = [
        'table_structure' => 'array',
        'column_count' => 'integer',
        'index_count' => 'integer',
        'foreign_key_count' => 'integer',
        'row_count' => 'integer',
        'snapshot_at' => 'datetime',
    ];

    // ==================== 访问器 ====================

    /**
     * 获取格式化后的表大小
     */
    public function getFormattedSizeAttribute(): string
    {
        if ($this->table_size === null) {
            return '-';
        }

        $size = $this->table_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }

    /**
     * 获取完整表名
     */
    public function getFullTableNameAttribute(): string
    {
        return $this->schema_name
            ? $this->schema_name . '.' . $this->table_name
            : $this->table_name;
    }

    // ==================== 业务方法 ====================

    /**
     * 获取字段列表
     *
     * @return array<int, array<string, mixed>>
     */
    public function getColumns(): array
    {
        return $this->table_structure['columns'] ?? [];
    }

    /**
     * 获取索引列表
     *
     * @return array<int, array<string, mixed>>
     */
    public function getIndexes(): array
    {
        return $this->table_structure['indexes'] ?? [];
    }

    /**
     * 获取外键列表
     *
     * @return array<int, array<string, mixed>>
     */
    public function getForeignKeys(): array
    {
        return $this->table_structure['foreign_keys'] ?? [];
    }

    /**
     * 检查是否存在指定字段
     */
    public function hasColumn(string $columnName): bool
    {
        $columns = $this->getColumns();

        foreach ($columns as $column) {
            if (($column['name'] ?? '') === $columnName) {
                return true;
            }
        }

        return false;
    }

    // ==================== 静态方法 ====================

    /**
     * 创建表快照
     *
     * @param string $connectionName 连接名称
     * @param string $tableName 表名
     * @param array<string, mixed> $structure 表结构数据
     * @param string|null $schemaName Schema名称
     * @return static
     */
    public static function createSnapshot(string $connectionName, string $tableName, array $structure, ?string $schemaName = null): static
    {
        return static::create([
            'connection_name' => $connectionName,
            'table_name' => $tableName,
            'schema_name' => $schemaName,
            'table_structure' => $structure,
            'column_count' => count($structure['columns'] ?? []),
            'index_count' => count($structure['indexes'] ?? []),
            'foreign_key_count' => count($structure['foreign_keys'] ?? []),
            'row_count' => $structure['row_count'] ?? null,
            'table_size' => $structure['table_size'] ?? null,
            'snapshot_at' => now(),
        ]);
    }

    /**
     * 获取表的最新快照
     *
     * @param string $connectionName 连接名称
     * @param string $tableName 表名
     * @param string|null $schemaName Schema名称
     * @return static|null
     */
    public static function getLatestSnapshot(string $connectionName, string $tableName, ?string $schemaName = null): ?static
    {
        return static::where('connection_name', $connectionName)
            ->where('table_name', $tableName)
            ->where('schema_name', $schemaName)
            ->orderBy('snapshot_at', 'desc')
            ->first();
    }

    /**
     * 获取表的所有快照
     *
     * @param string $connectionName 连接名称
     * @param string $tableName 表名
     * @param string|null $schemaName Schema名称
     * @param int $limit 限制数量
     * @return \Illuminate\Database\Eloquent\Collection<int, static>
     */
    public static function getTableSnapshots(string $connectionName, string $tableName, ?string $schemaName = null, int $limit = 10)
    {
        return static::where('connection_name', $connectionName)
            ->where('table_name', $tableName)
            ->where('schema_name', $schemaName)
            ->orderBy('snapshot_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * 清理过期快照
     *
     * @param int $keepDays 保留天数
     * @return int 删除数量
     */
    public static function cleanOldSnapshots(int $keepDays = 30): int
    {
        return static::where('snapshot_at', '<', now()->subDays($keepDays))->delete();
    }

    /**
     * 对比两个快照的差异
     *
     * @param static $oldSnapshot 旧快照
     * @param static $newSnapshot 新快照
     * @return array<string, mixed>
     */
    public static function diff(self $oldSnapshot, self $newSnapshot): array
    {
        $oldColumns = $oldSnapshot->getColumns();
        $newColumns = $newSnapshot->getColumns();

        $oldColumnNames = array_column($oldColumns, 'name');
        $newColumnNames = array_column($newColumns, 'name');

        $addedColumns = array_diff($newColumnNames, $oldColumnNames);
        $removedColumns = array_diff($oldColumnNames, $newColumnNames);

        return [
            'added_columns' => $addedColumns,
            'removed_columns' => $removedColumns,
            'column_count_diff' => $newSnapshot->column_count - $oldSnapshot->column_count,
            'index_count_diff' => $newSnapshot->index_count - $oldSnapshot->index_count,
            'row_count_diff' => ($newSnapshot->row_count ?? 0) - ($oldSnapshot->row_count ?? 0),
        ];
    }
}
