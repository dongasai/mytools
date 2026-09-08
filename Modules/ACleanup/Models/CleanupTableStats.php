<?php

namespace Modules\AClean\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 清理表统计模型
 *
 * 记录表的统计信息和扫描历史
 * field start
 *
 * @property int $id 主键ID
 * @property string $table_name 表名
 * @property int $record_count 记录总数
 * @property float $table_size_mb 表大小(MB)
 * @property float $index_size_mb 索引大小(MB)
 * @property float $data_free_mb 碎片空间(MB)
 * @property int $avg_row_length 平均行长度
 * @property int $auto_increment 自增值
 * @property string $oldest_record_time 最早记录时间
 * @property string $newest_record_time 最新记录时间
 * @property string $scan_time 扫描时间
 * @property \Carbon\Carbon $created_at 创建时间
 * @property \Carbon\Carbon $updated_at 更新时间
 *                                      field end
 */
class CleanupTableStats extends Model
{
    /**
     * 数据表名
     */
    protected $table = 'cleanup_table_stats';

    /**
     * 字段类型转换
     */
    protected $casts = [
        'record_count' => 'integer',
        'table_size_mb' => 'float',
        'index_size_mb' => 'float',
        'data_free_mb' => 'float',
        'avg_row_length' => 'integer',
        'auto_increment' => 'integer',
        'has_time_field' => 'boolean',
        'time_fields' => 'array',
        'has_user_field' => 'boolean',
        'user_fields' => 'array',
        'has_status_field' => 'boolean',
        'status_fields' => 'array',
        'last_scanned_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 按表名筛选
     */
    public function scopeByTable($query, string $tableName)
    {
        return $query->where('table_name', $tableName);
    }

    /**
     * 获取大表（记录数超过指定数量）
     */
    public function scopeLargeTables($query, int $minRecords = 10000)
    {
        return $query->where('record_count', '>', $minRecords);
    }

    /**
     * 获取有时间字段的表
     */
    public function scopeWithTimeFields($query)
    {
        return $query->where('has_time_field', true);
    }

    /**
     * 获取有用户字段的表
     */
    public function scopeWithUserFields($query)
    {
        return $query->where('has_user_field', true);
    }

    /**
     * 获取有状态字段的表
     */
    public function scopeWithStatusFields($query)
    {
        return $query->where('has_status_field', true);
    }

    /**
     * 获取表大小的可读格式
     */
    public function getTableSizeHumanAttribute(): string
    {
        $mb = $this->table_size_mb;

        if ($mb < 1) {
            return round($mb * 1024, 2).' KB';
        } elseif ($mb < 1024) {
            return round($mb, 2).' MB';
        } else {
            return round($mb / 1024, 2).' GB';
        }
    }

    /**
     * 获取总大小（表+索引）
     */
    public function getTotalSizeMbAttribute(): float
    {
        return $this->table_size_mb + $this->index_size_mb;
    }

    /**
     * 获取总大小的可读格式
     */
    public function getTotalSizeHumanAttribute(): string
    {
        $mb = $this->total_size_mb;

        if ($mb < 1) {
            return round($mb * 1024, 2).' KB';
        } elseif ($mb < 1024) {
            return round($mb, 2).' MB';
        } else {
            return round($mb / 1024, 2).' GB';
        }
    }
}
