<?php

namespace Modules\Application\DcatAdmin\Actions;

use Modules\Application\Enums\CONFIG_TYPE;
use Modules\DcatAdmin\DcatAdmin\RowAction;

/**
 * json固定键值对 动作
 */
class ConfigEmbedsEditAction extends RowAction
{
    protected $title = '修改数值';

    use ConfigEditAction;

    public function configEditForm(): string
    {
        return ConfigEmbedsEditForm::class;
    }

    public function allowed()
    {
        return $this->getRow()->type == CONFIG_TYPE::TYPE_EMBEDS->value();
    }
}
