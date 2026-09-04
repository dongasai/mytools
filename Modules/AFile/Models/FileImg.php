<?php

namespace Modules\AFile\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AFile\Img;

/**
 * 图片文件
 *
 * field start
 *
 * @property int $id
 * @property string $storage_disk 储存 disk
 * @property string $path 储存目录
 * @property int $user_id 用户iD
 * @property int $admin_id 管理员ID
 * @property string $re_type 关联类型
 * @property int $re_id 关联ID
 * @property string $o_name 原名
 * @property int $fsize 文件大小
 * @property int $width 图片宽度
 * @property int $height 图片高度
 * @property string $type1 图片类型
 * @property int $private 私人的； 0:公共的 ,1 私人的
 * @property string $status 状态:normal正常,linked已关联,dangling悬空
 * @property \Carbon\Carbon|null $used_at 使用时间
 * @property \Carbon\Carbon|null $dangling_at 悬空时间
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $deleted_at
 *                        field end
 */
class FileImg extends Model
{
    use SoftDeletes;

    // attrlist start
    protected $fillable = [
        'id',
        'storage_disk',
        'path',
        'user_id',
        'admin_id',
        're_type',
        're_id',
        'o_name',
        'fsize',
        'width',
        'height',
        'type1',
        'private',
        'status',
        'used_at',
        'dangling_at',
    ];

    // attrlist end
    protected $table = 'file_imgs';
}
