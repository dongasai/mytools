<?php

namespace Modules\Application\DcatAdmin\Actions;

use Modules\Application\Enums\CONFIG_TYPE;

/**
 * 字符串 动作
 */
class ConfigStringEditAction extends \Modules\DcatAdmin\DcatAdmin\RowAction
{
    protected $title = '修改数值';

    use ConfigEditAction;

    public function configEditForm(): string
    {
        return ConfigStringEditForm::class;
    }

    public function allowed()
    {
        //        dump($this->getRow()->type);
        return $this->getRow()->type == CONFIG_TYPE::TYPE_STRING->value();
    }
}
