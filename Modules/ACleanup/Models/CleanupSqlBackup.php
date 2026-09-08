<?php

namespace Modules\AClean\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SQL备份记录模型
 *
 * 存储INSERT语句到数据库表中
 * field start
 *
 * @property int $id 主键ID
 * @property int $backup_id 备份记录ID
 * @property string $table_name 表名
 * @property string $sql_content INSERT语句内容
 * @property int $records_count 记录数量
 * @property int $content_size 内容大小(字节)
 * @property string $content_hash 内容SHA256哈希
 * @property array $backup_conditions 备份条件
 * @property \Carbon\Carbon $created_at 创建时间
 *                                      field end*
 */
class CleanupSqlBackup extends Model
{
    /**
     * 数据表名
     */
    protected $table = 'cleanup_sql_backups';

    /**
     * 字段类型转换
     */
    protected $casts = [
        'backup_conditions' => 'array',
        'records_count' => 'integer',
        'content_size' => 'integer',
    ];

    /**
     * 隐藏字段
     */
    protected $hidden = [
        'sql_content', // 默认隐藏SQL内容，避免大量数据传输
    ];

    /**
     * 关联备份记录
     */
    public function backup(): BelongsTo
    {
        return $this->belongsTo(CleanupBackup::class, 'backup_id');
    }

    /**
     * 获取格式化的内容大小
     */
    public function getFormattedSizeAttribute(): string
    {
        $size = $this->content_size;

        if ($size < 1024) {
            return $size.' B';
        } elseif ($size < 1024 * 1024) {
            return round($size / 1024, 2).' KB';
        } elseif ($size < 1024 * 1024 * 1024) {
            return round($size / (1024 * 1024), 2).' MB';
        } else {
            return round($size / (1024 * 1024 * 1024), 2).' GB';
        }
    }

    /**
     * 获取SQL内容预览（前100个字符）
     */
    public function getSqlPreviewAttribute(): string
    {
        if (empty($this->sql_content)) {
            return '';
        }

        $preview = substr($this->sql_content, 0, 100);
        if (strlen($this->sql_content) > 100) {
            $preview .= '...';
        }

        return $preview;
    }

    /**
     * 验证内容哈希
     */
    public function verifyContentHash(): bool
    {
        if (empty($this->sql_content) || empty($this->content_hash)) {
            return false;
        }

        $currentHash = hash('sha256', $this->sql_content);

        return $currentHash === $this->content_hash;
    }

    /**
     * 作用域：按备份ID筛选
     */
    public function scopeByBackup($query, int $backupId)
    {
        return $query->where('backup_id', $backupId);
    }

    /**
     * 作用域：按表名筛选
     */
    public function scopeByTable($query, string $tableName)
    {
        return $query->where('table_name', $tableName);
    }

    /**
     * 作用域：按记录数量排序
     */
    public function scopeOrderByRecords($query, string $direction = 'desc')
    {
        return $query->orderBy('records_count', $direction);
    }

    /**
     * 作用域：按内容大小排序
     */
    public function scopeOrderBySize($query, string $direction = 'desc')
    {
        return $query->orderBy('content_size', $direction);
    }

    /**
     * 获取统计信息
     */
    public static function getStats(): array
    {
        return [
            'total_count' => static::count(),
            'total_records' => static::sum('records_count'),
            'total_size' => static::sum('content_size'),
            'avg_records_per_backup' => static::avg('records_count'),
            'avg_size_per_backup' => static::avg('content_size'),
        ];
    }

    /**
     * 获取按表名分组的统计
     */
    public static function getTableStats(): array
    {
        return static::selectRaw('
            table_name,
            COUNT(*) as backup_count,
            SUM(records_count) as total_records,
            SUM(content_size) as total_size,
            AVG(records_count) as avg_records,
            AVG(content_size) as avg_size
        ')
            ->groupBy('table_name')
            ->orderBy('total_size', 'desc')
            ->get()
            ->toArray();
    }
}
