<?php

namespace Modules\DcatAdmin\DcatAdmin\Metrics;

use Dcat\Admin\Widgets\Widget;

class Image extends Widget
{
    protected $view = 'module_dcatadmin::widgets.image';

    public $src = '';

    public $height = '100px';

    public $width = '100px';

    public function __construct($imgSrc, $height = '100px', $width = '100px')
    {
        $this->src = $imgSrc;
        $this->height = $height;
        $this->width = $width;
    }

    public function defaultVariables()
    {
        return [
            'src' => $this->src,
            'width' => $this->width,
            'height' => $this->height,
            'attributes' => $this->formatHtmlAttributes(),
            'options' => $this->options,
            'class' => $this->getElementClass(),
            'selector' => $this->getElementSelector(),
        ];
    }
}
