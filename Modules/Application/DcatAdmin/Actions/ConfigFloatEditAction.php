<?php

namespace Modules\Application\DcatAdmin\Actions;

use Modules\Application\Enums\CONFIG_TYPE;

/**
 * 浮点数字修改数值 动作
 */
class ConfigFloatEditAction extends \Modules\DcatAdmin\DcatAdmin\RowAction
{
    use ConfigEditAction;

    protected $title = '修改数值';

    public function configEditForm(): string
    {
        return ConfigFloatEditForm::class;
    }

    public function allowed()
    {
        return $this->getRow()->type == CONFIG_TYPE::TYPE_FLOAT->value();
    }
}
