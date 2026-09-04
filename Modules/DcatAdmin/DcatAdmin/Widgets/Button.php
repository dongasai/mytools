<?php

namespace Modules\DcatAdmin\DcatAdmin\Widgets;

use Dcat\Admin\Widgets\Widget;
use Illuminate\Contracts\Support\Renderable;

class Button extends Widget implements Renderable
{
    protected $display = true;

    public $title = '按钮';

    public function __construct($title = '按钮')
    {
        $this->title = $title;

    }

    public function display($value)
    {
        $this->display = $value;

        return $this;
    }

    /**
     * Render refresh button of grid.
     *
     * @return string
     */
    public function render()
    {
        if (! $this->display) {
            return;
        }

        return <<<EOT
<button class="btn btn-primary  btn-mini" style="margin-right:3px">
    <span class="d-none d-sm-inline"> {$this->title}  </span>
</button>
EOT;
    }
}
