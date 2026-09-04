<?php

namespace Modules\DcatAdmin\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 管理员
 *
 * field start
 *
 * @property int $id
 * @property string $username
 * @property string $password
 * @property string $name
 * @property string $avatar
 * @property string $remember_token
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *                                      field end
 */
class AdminUser extends Model
{
    // attrlist start
    protected $fillable = [
        'username',
        'password',
        'name',
        'avatar',
        'remember_token',
    ];
    // attrlist end

    /**
     * 属性类型转换
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 隐藏属性
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
}
