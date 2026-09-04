<?php

namespace Modules\DcatAdmin\DcatAdmin\Traits;

use Modules\DcatAdmin\DcatAdmin\AdminController;

trait Controller
{
    /**
     * @var AdminController
     */
    public $controller;

    public function setController($c)
    {
        $this->controller = $c;

        return $this;
    }
}
