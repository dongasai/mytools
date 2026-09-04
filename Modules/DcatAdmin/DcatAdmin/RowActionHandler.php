<?php

namespace Modules\DcatAdmin\DcatAdmin;

use Dcat\Admin\Grid\RowAction as BaseRowAction;

/**
 * 行操作,列表页面,覆盖渲染
 */
class RowActionHandler extends BaseRowAction
{

    final public function render()
    {

        if (! $this->allowed() || !$this->isShow()) {
            return '';
        }

        return $this->render2();
    }


    public function render2()
    {
        return parent::render();
    }



    /**
     * 是否显示
     */
    public function isShow(){
        return true;
    }
}
