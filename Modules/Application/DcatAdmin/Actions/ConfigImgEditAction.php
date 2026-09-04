<?php

namespace Modules\Application\DcatAdmin\Actions;

use Modules\Application\Enums\CONFIG_TYPE;

/**
 * 图片 动作
 */
class ConfigImgEditAction extends \Modules\DcatAdmin\DcatAdmin\RowAction
{
    protected $title = '修改数值';

    use ConfigEditAction;

    public function configEditForm(): string
    {
        return ConfigImgEditForm::class;
    }

    public function allowed()
    {
        return $this->getRow()->type == CONFIG_TYPE::TYPE_IMG->value();
    }
}
