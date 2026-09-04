<?php

namespace Modules\Application\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modules\Application\Models\ApplicationConfig
 *
 * field start
 *
 * @property int $id
 * @property string $keyname key
 * @property string $is_client 是否给客户端
 * @property string $title 标题
 * @property int $type 类型 详情见枚举
 * @property string $value 值
 * @property string $group 分组
 * @property string $group2 分组2
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at 删除时间
 * @property string $desc 描述
 * @property string $options 其他配置
 *                           field end
 */
class ApplicationConfig extends Model
{
    use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'application_configs';

    // attrlist start
    protected $fillable = [
        'id',
        'keyname',
        'is_client',
        'title',
        'type',
        'value',
        'group',
        'group2',
        'desc',
        'options',
    ];
    // attrlist end

}
