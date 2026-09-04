<?php

namespace Modules\Application\DcatAdmin\Actions;

use Modules\Application\Enums\CONFIG_TYPE;

/**
 * 数字 动作
 */
class ConfigIntEditAction extends \Modules\DcatAdmin\DcatAdmin\RowAction
{
    protected $title = '修改数值';

    use ConfigEditAction;

    public function configEditForm()
    {
        return ConfigIntEditForm::class;
    }

    public function allowed()
    {
        return $this->getRow()->type == CONFIG_TYPE::TYPE_INT->valueInt();
    }
}
