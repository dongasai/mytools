<?php

namespace Modules\AFile\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 存储配置历史记录模型
 *
 * field start
 *
 * @property int $id 主键
 * @property int $config_id 关联的存储配置ID
 * @property string $old_driver 旧存储驱动
 * @property string $new_driver 新存储驱动
 * @property array $old_config 旧配置值
 * @property array $new_config 新配置值
 * @property bool $old_status 旧状态
 * @property bool $new_status 新状态
 * @property string $changed_at 变更时间
 * @property int $changed_by 变更人ID
 * @property string $change_reason 变更原因
 * @property \Carbon\Carbon $created_at 创建时间
 * @property \Carbon\Carbon $updated_at 更新时间
 *                                      field end
 */
class FileStorageConfigHistory extends Model
{
    /**
     * 表名
     */
    protected $table = 'file_storage_config_histories';

    /**
     * 主键
     */
    protected $primaryKey = 'id';

    /**
     * 可批量赋值的字段
     */
    protected $fillable = [
        'config_id',
        'old_driver',
        'new_driver',
        'old_config',
        'new_config',
        'old_status',
        'new_status',
        'changed_by',
        'change_reason',
    ];

    /**
     * 类型转换
     */
    protected $casts = [
        'old_config' => 'array',
        'new_config' => 'array',
        'old_status' => 'boolean',
        'new_status' => 'boolean',
    ];

    /**
     * 自动维护时间戳
     */
    public $timestamps = false;

    /**
     * 关联的存储配置
     */
    public function config()
    {
        return $this->belongsTo(FileStorageConfig::class, 'config_id');
    }
}
