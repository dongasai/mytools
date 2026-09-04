<?php

namespace Modules\DcatAdmin\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modules\DcatAdmin\Models\AdminMenu
 *
 * field start
 *
 * @property int $id
 * @property int $parent_id
 * @property int $order
 * @property string $title
 * @property string $icon
 * @property string $uri
 * @property string $extension
 * @property int $show
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *                                      field end
 */
class AdminMenu extends Model
{
    protected $table = 'admin_menu';

    // attrlist start
    protected $fillable = [
        'id',
        'parent_id',
        'order',
        'title',
        'icon',
        'uri',
        'extension',
        'show',
    ];
    // attrlist end

}
