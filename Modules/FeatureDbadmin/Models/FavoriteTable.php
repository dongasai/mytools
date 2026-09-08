<?php

namespace Modules\FeatureDbadmin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\DcatAdmin\Models\Administrator;

/**
 * 收藏的表模型
 *
 * @property int $id 收藏ID
 * @property int $user_id 用户ID
 * @property string $connection_name 连接名称
 * @property string $table_name 表名
 * @property string|null $schema_name Schema名称
 * @property string|null $alias 别名
 * @property string|null $notes 备注
 * @property \Carbon\Carbon|null $created_at 创建时间
 * @property \Carbon\Carbon|null $updated_at 更新时间
 */
class FavoriteTable extends Model
{
    use HasFactory;

    /**
     * 表名
     */
    protected $table = 'feature_dbadmin_favorite_tables';

    /**
     * 可填充字段
     */
    protected $fillable = [
        'user_id',
        'connection_name',
        'table_name',
        'schema_name',
        'alias',
        'notes',
    ];

    /**
     * 字段类型转换
     */
    protected $casts = [];

    // ==================== 模型关系 ====================

    /**
     * 获取所属用户
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(Administrator::class, 'user_id');
    }

    // ==================== 访问器 ====================

    /**
     * 获取显示名称
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->alias) {
            return $this->alias;
        }

        return $this->schema_name
            ? $this->schema_name . '.' . $this->table_name
            : $this->table_name;
    }

    /**
     * 获取完整表名（包含schema）
     */
    public function getFullTableNameAttribute(): string
    {
        return $this->schema_name
            ? $this->schema_name . '.' . $this->table_name
            : $this->table_name;
    }

    // ==================== 业务方法 ====================

    /**
     * 检查是否属于指定用户
     */
    public function belongsToUser(int $userId): bool
    {
        return $this->user_id === $userId;
    }

    /**
     * 获取唯一标识
     */
    public function getUniqueKey(): string
    {
        return implode(':', [
            $this->connection_name,
            $this->schema_name ?? '',
            $this->table_name,
        ]);
    }

    // ==================== 静态方法 ====================

    /**
     * 获取用户的收藏表列表
     *
     * @param int $userId 用户ID
     * @param string|null $connectionName 连接名称过滤
     * @return \Illuminate\Database\Eloquent\Collection<int, static>
     */
    public static function getUserFavorites(int $userId, ?string $connectionName = null)
    {
        $query = static::where('user_id', $userId);

        if ($connectionName !== null) {
            $query->where('connection_name', $connectionName);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * 检查表是否已被收藏
     *
     * @param int $userId 用户ID
     * @param string $connectionName 连接名称
     * @param string $tableName 表名
     * @param string|null $schemaName Schema名称
     * @return bool
     */
    public static function isFavorited(int $userId, string $connectionName, string $tableName, ?string $schemaName = null): bool
    {
        return static::where('user_id', $userId)
            ->where('connection_name', $connectionName)
            ->where('table_name', $tableName)
            ->where('schema_name', $schemaName)
            ->exists();
    }

    /**
     * 添加收藏
     *
     * @param int $userId 用户ID
     * @param string $connectionName 连接名称
     * @param string $tableName 表名
     * @param string|null $schemaName Schema名称
     * @param array<string, mixed> $extraData 额外数据
     * @return static
     */
    public static function addFavorite(int $userId, string $connectionName, string $tableName, ?string $schemaName = null, array $extraData = []): static
    {
        $data = array_merge([
            'user_id' => $userId,
            'connection_name' => $connectionName,
            'table_name' => $tableName,
            'schema_name' => $schemaName,
        ], $extraData);

        return static::create($data);
    }

    /**
     * 取消收藏
     *
     * @param int $userId 用户ID
     * @param string $connectionName 连接名称
     * @param string $tableName 表名
     * @param string|null $schemaName Schema名称
     * @return bool
     */
    public static function removeFavorite(int $userId, string $connectionName, string $tableName, ?string $schemaName = null): bool
    {
        return static::where('user_id', $userId)
            ->where('connection_name', $connectionName)
            ->where('table_name', $tableName)
            ->where('schema_name', $schemaName)
            ->delete() > 0;
    }
}
