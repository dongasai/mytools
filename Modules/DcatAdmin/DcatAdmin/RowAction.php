<?php

namespace Modules\DcatAdmin\DcatAdmin;

use Dcat\Admin\Grid\RowAction as BaseRowAction;

/**
 * 行操作,列表页面,覆盖渲染
 */
class RowAction extends BaseRowAction
{
    /**
     * @var AdminController
     */
    public $controller;

    public function render()
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

    public function setController($c)
    {
        $this->controller = $c;

        return $this;
    }

    private function isShow()
    {
        return true;
    }
}
