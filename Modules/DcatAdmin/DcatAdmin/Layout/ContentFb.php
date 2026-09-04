<?php

namespace Modules\DcatAdmin\DcatAdmin\Layout;

use Dcat\Admin\Layout\Content;

class ContentFb extends Content
{
    public function full()
    {
        return $this->view('module_dcatadmin::layouts.full-contentb');
    }
}
