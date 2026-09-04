<?php

namespace Modules\Application\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

/**
 * Modules\Application\Models\ApplicationDict
 *
 * field start
 *
 * @property int $id
 * @property string $dict_type 字典类型
 * @property string $dict_label 字典标签
 * @property string $dict_value 字典值
 * @property int $dict_sort 排序
 * @property int $status 状态
 * @property int $is_client 是否允许客户端获取
 * @property string $remark 备注
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *                           field end
 */
class ApplicationDict extends Model
{
    use HasDateTimeFormatter;

    protected $table = 'application_dict';

    // attrlist start
    protected $fillable = [
        'dict_type',
        'dict_label',
        'dict_value',
        'dict_sort',
        'status',
        'is_client',
        'remark',
    ];
    // attrlist end

    protected $casts = [
        'status' => 'integer',
        'dict_sort' => 'integer',
        'is_client' => 'integer',
    ];
}
