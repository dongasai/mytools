<?php

namespace Modules\AClean\Models;

use Modules\AClean\Enums\BACKUP_TYPE;
use Modules\AClean\Enums\COMPRESSION_TYPE;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 清理备份文件模型
 *
 * 记录备份文件的详细信息
 * field start
 *
 * @property int $id 主键ID
 * @property int $backup_id 备份记录ID
 * @property string $table_name 表名
 * @property string $file_name 文件名
 * @property string $file_path 文件路径
 * @property int $file_size 文件大小(字节)
 * @property string $file_hash 文件SHA256哈希
 * @property int $backup_type 备份类型:1SQL,2JSON,3CSV
 * @property int $compression_type 压缩类型:1none,2gzip,3zip
 * @property \Carbon\Carbon $created_at 创建时间
 *                                      field end
 */
class CleanupBackupFile extends Model
{
    /**
     * 数据表名
     */
    protected $table = 'cleanup_backup_files';

    /**
     * 可填充字段
     */
    protected $fillable = [
        'backup_id',
        'table_name',
        'file_name',
        'file_path',
        'file_size',
        'file_hash',
        'records_count',
        'backup_type',
        'compression_type',
        'is_completed',
    ];

    /**
     * 字段类型转换
     */
    protected $casts = [
        'backup_id' => 'integer',
        'file_size' => 'integer',
        'records_count' => 'integer',
        'is_completed' => 'boolean',
        'backup_type' => 'integer',
        'compression_type' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 获取备份类型枚举
     */
    public function getBackupTypeEnumAttribute(): BACKUP_TYPE
    {
        return BACKUP_TYPE::from($this->backup_type);
    }

    /**
     * 获取压缩类型枚举
     */
    public function getCompressionTypeEnumAttribute(): COMPRESSION_TYPE
    {
        return COMPRESSION_TYPE::from($this->compression_type);
    }

    /**
     * 关联备份记录
     */
    public function backup(): BelongsTo
    {
        return $this->belongsTo(CleanupBackup::class, 'backup_id');
    }

    /**
     * 按表名筛选
     */
    public function scopeByTable($query, string $tableName)
    {
        return $query->where('table_name', $tableName);
    }

    /**
     * 按备份类型筛选
     */
    public function scopeByBackupType($query, BACKUP_TYPE $backupType)
    {
        return $query->where('backup_type', $backupType->value);
    }

    /**
     * 按压缩类型筛选
     */
    public function scopeByCompressionType($query, COMPRESSION_TYPE $compressionType)
    {
        return $query->where('compression_type', $compressionType->value);
    }

    /**
     * 获取文件大小的可读格式
     */
    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }
}
