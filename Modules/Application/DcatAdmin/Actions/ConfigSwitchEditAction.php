<?php

namespace Modules\Application\DcatAdmin\Actions;

use Modules\Application\Enums\CONFIG_TYPE;

/**
 * bool修改 动作
 */
class ConfigSwitchEditAction extends \Modules\DcatAdmin\DcatAdmin\RowAction
{
    protected $title = '修改数值';

    use ConfigEditAction;

    public function allowed()
    {

        return $this->getRow()->type == CONFIG_TYPE::TYPE_BOOL->value();
    }

    public function configEditForm(): string
    {
        return ConfigSwitchEditForm::class;
    }
}
