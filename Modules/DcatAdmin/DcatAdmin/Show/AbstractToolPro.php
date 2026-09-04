<?php

namespace Modules\DcatAdmin\DcatAdmin\Show;

use Dcat\Admin\Show\AbstractTool;

class AbstractToolPro extends AbstractTool
{
    public function title(): string
    {
        return __($this->title);
    }

    public function confirm()
    {
        return [];
    }
}
