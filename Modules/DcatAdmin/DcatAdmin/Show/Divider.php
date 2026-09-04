<?php

namespace Modules\DcatAdmin\DcatAdmin\Show;

use Dcat\Admin\Show\AbstractField;

class Divider extends AbstractField
{
    public function render()
    {
        return <<<HTML
<div class="mt-2 text-center mb-2 form-divider">
  <span>{$this->name}</span>
</div>
HTML;
    }
}
