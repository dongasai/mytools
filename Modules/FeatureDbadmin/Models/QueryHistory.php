<?php

namespace Modules\FeatureDbadmin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * SQL查询历史记录模型
 *
 * @property int $id 历史记录ID
 * @property int $user_id 用户ID
 * @property string $connection_name 连接名称
 * @property string $sql_query SQL查询语句
 * @property string|null $query_type 查询类型(SELECT/INSERT/UPDATE/DELETE/OTHER)
 * @property int|null $execution_time 执行时间(毫秒)
 * @property int|null $row_count 返回行数
 * @property string $status 执行状态(success/error)
 * @property string|null $error_message 错误信息
 * @property \Carbon\Carbon $executed_at 执行时间
 * @property \Carbon\Carbon|null $created_at 创建时间
 * @property \Carbon\Carbon|null $updated_at 更新时间
 */
class QueryHistory extends Model
{
    use HasFactory;

    /**
     * 表名
     */
    protected $table = 'feature_dbadmin_query_histories';

    /**
     * 可填充字段
     */
    protected $fillable = [
        'user_id',
        'connection_name',
        'sql_query',
        'query_type',
        'execution_time',
        'row_count',
        'status',
        'error_message',
        'executed_at',
    ];

    /**
     * 字段类型转换
     */
    protected $casts = [
        'execution_time' => 'integer',
        'row_count' => 'integer',
        'executed_at' => 'datetime',
    ];

    // ==================== 访问器 ====================

    /**
     * 获取格式化后的执行时间
     */
    public function getFormattedExecutionTimeAttribute(): string
    {
        if ($this->execution_time === null) {
            return '-';
        }

        if ($this->execution_time < 1000) {
            return $this->execution_time . 'ms';
        }

        return round($this->execution_time / 1000, 2) . 's';
    }

    /**
     * 获取SQL语句摘要
     */
    public function getSqlSummaryAttribute(): string
    {
        $sql = trim($this->sql_query);
        $sql = preg_replace('/\s+/', ' ', $sql);

        if (strlen($sql) > 100) {
            return substr($sql, 0, 100) . '...';
        }

        return $sql;
    }

    // ==================== 静态方法 ====================

    /**
     * 记录查询历史
     *
     * @param array<string, mixed> $data 查询数据
     * @return static
     */
    public static function record(array $data): static
    {
        $data['executed_at'] = $data['executed_at'] ?? now();

        return static::create($data);
    }

    /**
     * 获取用户的查询历史
     *
     * @param int $userId 用户ID
     * @param int $limit 限制条数
     * @return \Illuminate\Database\Eloquent\Collection<int, static>
     */
    public static function getUserHistory(int $userId, int $limit = 50)
    {
        return static::where('user_id', $userId)
            ->orderBy('executed_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * 按查询类型统计
     *
     * @param int $userId 用户ID
     * @param int $days 统计天数
     * @return array<string, int>
     */
    public static function getStatsByType(int $userId, int $days = 7): array
    {
        return static::where('user_id', $userId)
            ->where('executed_at', '>=', now()->subDays($days))
            ->selectRaw('query_type, COUNT(*) as count')
            ->groupBy('query_type')
            ->pluck('count', 'query_type')
            ->toArray();
    }
}
