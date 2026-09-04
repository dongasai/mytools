<?php

namespace Modules\DcatAdmin\DcatAdmin\Grid\Tool;

use Dcat\Admin\Grid\Tools\AbstractTool;

class HrefAbstractTool extends AbstractTool
{
    public function html()
    {
        $this->appendHtmlAttribute('class', $this->style);

        return <<<HTML
<a {$this->formatHtmlAttributes()}>{$this->title}</a>
HTML;
    }
}
