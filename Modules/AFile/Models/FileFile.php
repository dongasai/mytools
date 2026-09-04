<?php

namespace Modules\AFile\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 文件  - 文件
 * field start
 *
 * @property int $id
 * @property string $storage_disk 储存disk
 * @property int $user_id 用户ID
 * @property string $path 储存目录
 * @property string $re_type 关联类型
 * @property int $re_id 关联ID
 * @property string $o_name 原名
 * @property int $fsize 文件大小
 * @property string $type1 文件类型
 * @property string $status 状态:normal正常,linked已关联,dangling悬空
 * @property \Carbon\Carbon|null $used_at 使用时间
 * @property \Carbon\Carbon|null $dangling_at 悬空时间
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $deleted_at
 *                                      field end
 */
class FileFile extends Model
{
    use SoftDeletes;
    // attrlist start
    protected $fillable = [
        'id',
        'storage_disk',
        'user_id',
        'path',
        're_type',
        're_id',
        'o_name',
        'fsize',
        'type1',
        'status',
        'used_at',
        'dangling_at',
        'deleted_at',
    ];
    // attrlist end

    /**
     * 关联存储配置
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function storageConfig()
    {
        return $this->belongsTo(FileStorageConfig::class, 'storage_disk', 'name');
    }
}
