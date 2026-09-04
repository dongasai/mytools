<?php

namespace Modules\DcatAdmin\DcatAdmin\Show;

use Dcat\Admin\Show\AbstractTool;

/**
 * 详情页面的动作
 */
class AbstractTool2 extends AbstractTool
{
    public function getRow()
    {
        return $this->parent->model();
    }

    public function title(): string
    {
        return __($this->title);
    }

    public function render()
    {

        if (! $this->allowed()) {
            return '';
        }

        return $this->render2();
    }
}
