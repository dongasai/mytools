<?php

namespace Modules\Application\DcatAdmin\Actions;

use Modules\Application\Enums\CONFIG_TYPE;

/**
 * json键值对 动作
 */
class ConfigJsonEditAction extends \Modules\DcatAdmin\DcatAdmin\RowAction
{
    protected $title = '修改数值';

    use ConfigEditAction;

    public function configEditForm(): string
    {
        return ConfigJsonEditForm::class;
    }

    public function allowed()
    {
        return $this->getRow()->type == CONFIG_TYPE::TYPE_JSON->value();
    }
}
