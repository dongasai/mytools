<?php

namespace Modules\FeatureDbadmin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\DcatAdmin\Models\Administrator;

/**
 * 保存的SQL查询模型
 *
 * @property int $id 查询ID
 * @property int $user_id 用户ID
 * @property string $name 查询名称
 * @property string|null $description 描述
 * @property string $connection_name 连接名称
 * @property string $sql_query SQL查询语句
 * @property array|null $tags 标签
 * @property bool $is_public 是否公开
 * @property int $use_count 使用次数
 * @property \Carbon\Carbon|null $last_used_at 最后使用时间
 * @property \Carbon\Carbon|null $created_at 创建时间
 * @property \Carbon\Carbon|null $updated_at 更新时间
 * @property \Carbon\Carbon|null $deleted_at 删除时间
 */
class SavedQuery extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * 表名
     */
    protected $table = 'feature_dbadmin_saved_queries';

    /**
     * 可填充字段
     */
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'connection_name',
        'sql_query',
        'tags',
        'is_public',
        'use_count',
        'last_used_at',
    ];

    /**
     * 字段类型转换
     */
    protected $casts = [
        'tags' => 'array',
        'is_public' => 'boolean',
        'use_count' => 'integer',
        'last_used_at' => 'datetime',
    ];

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
     * 获取标签字符串
     */
    public function getTagsStringAttribute(): string
    {
        if (empty($this->tags)) {
            return '';
        }

        return implode(', ', $this->tags);
    }

    /**
     * 获取SQL摘要
     */
    public function getSqlSummaryAttribute(): string
    {
        $sql = trim($this->sql_query);
        $sql = preg_replace('/\s+/', ' ', $sql);

        if (strlen($sql) > 150) {
            return substr($sql, 0, 150) . '...';
        }

        return $sql;
    }

    // ==================== 业务方法 ====================

    /**
     * 增加使用次数
     */
    public function incrementUsage(): void
    {
        $this->increment('use_count');
        $this->last_used_at = now();
        $this->save();
    }

    /**
     * 检查是否属于指定用户
     */
    public function belongsToUser(int $userId): bool
    {
        return $this->user_id === $userId;
    }

    // ==================== 静态方法 ====================

    /**
     * 获取用户的保存查询
     *
     * @param int $userId 用户ID
     * @param bool $includePublic 是否包含公开查询
     * @return \Illuminate\Database\Eloquent\Collection<int, static>
     */
    public static function getUserQueries(int $userId, bool $includePublic = true)
    {
        $query = static::where(function ($q) use ($userId, $includePublic) {
            $q->where('user_id', $userId);
            if ($includePublic) {
                $q->orWhere('is_public', true);
            }
        });

        return $query->orderBy('name')->get();
    }

    /**
     * 按标签搜索
     *
     * @param string $tag 标签
     * @param int|null $userId 用户ID(可选)
     * @return \Illuminate\Database\Eloquent\Collection<int, static>
     */
    public static function searchByTag(string $tag, ?int $userId = null)
    {
        $query = static::where('tags', 'like', '%"' . $tag . '"%');

        if ($userId !== null) {
            $query->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                    ->orWhere('is_public', true);
            });
        }

        return $query->orderBy('use_count', 'desc')->get();
    }

    /**
     * 获取热门查询
     *
     * @param int $limit 数量限制
     * @return \Illuminate\Database\Eloquent\Collection<int, static>
     */
    public static function getPopularQueries(int $limit = 10)
    {
        return static::where('is_public', true)
            ->orderBy('use_count', 'desc')
            ->limit($limit)
            ->get();
    }
}
