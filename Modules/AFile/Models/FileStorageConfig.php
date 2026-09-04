<?php

namespace Modules\AFile\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * field start
 *
 * @property int $id 主键
 * @property string $name 存储磁盘名称，唯一
 * @property string $driver 存储驱动（local, s3, oss等）
 * @property array $config 配置值，JSON格式
 * @property string $description 配置描述
 * @property bool $is_default 是否默认存储，1表示是，0表示否
 * @property bool $is_temp 是否用于临时存储，1表示是，0表示否
 * @property bool $status 状态：1-启用，0-禁用
 * @property string $env 环境（development, testing, production）
 * @property \Carbon\Carbon $created_at 创建时间
 * @property \Carbon\Carbon $updated_at 更新时间
 * @property int $created_by 创建人ID
 * @property int $updated_by 更新人ID
 *                           field end
 */
class FileStorageConfig extends Model
{
    /**
     * 表名
     */
    protected $table = 'file_storage_configs';

    /**
     * 主键
     */
    protected $primaryKey = 'id';

    /**
     * 可批量赋值的字段
     */
    protected $fillable = [
        'name',
        'driver',
        'config',
        'description',
        'is_default',
        'is_temp',
        'status',
        'env',
        'created_by',
        'updated_by',
    ];

    /**
     * 类型转换
     */
    protected $casts = [
        'config' => 'array',
        'is_default' => 'boolean',
        'is_temp' => 'boolean',
        'status' => 'boolean',
    ];

    /**
     * 自动维护时间戳
     */
    public $timestamps = true;

    /**
     * 配置变更历史
     */
    public function histories()
    {
        return $this->hasMany(FileStorageConfigHistory::class, 'config_id');
    }
}
