<?php

namespace Modules\Application\DcatAdmin\Actions;

use Modules\Application\Enums\CONFIG_TYPE;

/**
 * 时间,秒 动作
 */
class ConfigTimeEditAction extends \Modules\DcatAdmin\DcatAdmin\RowAction
{
    protected $title = '修改数值';

    use ConfigEditAction;

    public function allowed()
    {
        return $this->getRow()->type == CONFIG_TYPE::TYPE_TIME->valueInt();
    }

    public function configEditForm(): string
    {
        return ConfigTimeEditForm::class;
    }
}
