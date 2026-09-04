<?php

namespace Modules\DcatAdmin\DcatAdmin\Traits;

use Modules\DcatAdmin\DcatAdmin\LazyRenderable\AdminInfo;

/**
 * 管理员ID
 */
trait AdminId
{
    public function columnAdminId($field = 'admin_id', $label = '管理员')
    {
        $this->grid->column($field, $label)->expand(function () use ($field) {
            return AdminInfo::make([
                'admin_id' => $this->$field,
            ]);
        });
    }
}
