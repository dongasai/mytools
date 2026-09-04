<?php

namespace Modules\DcatAdmin\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 后台操作日志
 *
 * Class AdminActionlog
 *
 * field start
 *
 * @property int $id
 * @property string $type1
 * @property string $unid
 * @property int $admin_id 操作的Admin ID
 * @property string $object_class 操作对象
 * @property string $url 网址
 * @property array $before 操作之前
 * @property array $after 操作之后
 * @property int $status 状态
 * @property string $p1 参数1
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $created_at
 *                                      field end
 */
class AdminActionlog extends Model
{
    /**
     * 显式定义表名（数据库表名有下划线）
     */
    protected $table = 'admin_action_logs';

    // attrlist start
    protected $fillable = [
        'id',
        'type1',
        'unid',
        'admin_id',
        'object_class',
        'url',
        'before',
        'after',
        'status',
        'p1',
    ];
    // attrlist end

    protected $casts = [
        'before' => 'array',
        'after' => 'array',
    ];

    public function admin()
    {
        return $this->hasOne(\Modules\DcatAdmin\Models\Administrator::class, 'id', 'admin_id');
    }
}
