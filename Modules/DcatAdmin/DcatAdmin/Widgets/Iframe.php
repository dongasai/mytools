<?php

namespace Modules\DcatAdmin\DcatAdmin\Widgets;

use Dcat\Admin\Widgets\Widget;

class Iframe extends Widget
{
    public $width = '1000px';

    public $height = '500px';

    public $view = 'module_dcatadmin::widgets.iframe';

    protected $title;

    protected $url;

    public function __construct($url, $width = '1000px')
    {
        $this->url = $url;
        $this->width = $width;
    }

    public function defaultVariables()
    {
        return [
            'width' => $this->width,
            'height' => $this->height,
            'url' => $this->url,
        ];
    }
}
