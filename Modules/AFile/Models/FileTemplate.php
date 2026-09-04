<?php

namespace Modules\AFile\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 文件模板
 *
 * field start
 *
 * @property int $id
 * @property string $unid 标识
 * @property int $file_id
 * @property string $title 模板标题
 * @property string $desc 描述
 * @property string $status
 * @property string $group 分组
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $deleted_at
 *                                      field end
 */
class FileTemplate extends Model
{
    use SoftDeletes;

    // attrlist start
    protected $fillable = [
        'id',
        'unid',
        'file_id',
        'title',
        'desc',
        'status',
        'group',
    ];

    // attrlist end
    /**
     * 无效
     */
    const STATUS_0 = 0;

    /**
     * 有效
     */
    const STATUS_1 = 1;

    protected $table = 'file_template';

    public function file()
    {
        return $this->hasOne(FileImg::class, 'id', 'file_id');
    }
}
