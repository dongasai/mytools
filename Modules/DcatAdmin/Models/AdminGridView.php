<?php

namespace Modules\DcatAdmin\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Application\Enums\VIEW_TYPE;
use Modules\System\Models\Modules;

/**
 * 后台操作日志
 *
 * Class AdminActionlog
 *
 * field start
 *
 * @property int $id
 * @property int $admin_id 操作的Admin ID
 * @property Modules\System\Enums\VIEW_TYPE $type1
 * @property string $title 视图标题
 * @property string $router_name 路由名字
 * @property array $p1 参数1
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $created_at
 *                                      field end
 */
class AdminGridView extends Model
{
    // attrlist start
    protected $fillable = [
        'id',
        'admin_id',
        'type1',
        'title',
        'router_name',
        'p1',
    ];
    // attrlist end

    protected $casts = [
        'p1' => 'array',
        'type1' => VIEW_TYPE::class,
    ];

    public function admin()
    {
        return $this->hasOne(\Modules\DcatAdmin\Models\Administrator::class, 'id', 'admin_id');
    }
}
