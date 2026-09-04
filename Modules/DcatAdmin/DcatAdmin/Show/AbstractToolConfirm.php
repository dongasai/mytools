<?php

namespace Modules\DcatAdmin\DcatAdmin\Show;

use Dcat\Admin\Show\AbstractTool;

class AbstractToolConfirm extends AbstractTool
{
    protected $confirm_title = '';

    protected $confirm_content = '';

    public function title(): string
    {
        return __($this->title);
    }

    public function confirm()
    {
        return [
            __($this->confirm_title),
            __($this->confirm_content),
        ];
    }
}
