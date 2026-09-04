<?php

namespace Modules\DcatAdmin\Models;

/**
 * Modules\DcatAdmin\Models\Administrator
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
class Administrator extends \Dcat\Admin\Models\Administrator
{
    protected $table = 'admin_users';

    // attrlist start
    protected $fillable = [
        'id',
        'username',
        'password',
        'name',
        'avatar',
        'remember_token',
    ];
    // attrlist end

}
