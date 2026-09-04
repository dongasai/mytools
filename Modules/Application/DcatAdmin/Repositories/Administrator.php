<?php

namespace Modules\Application\DcatAdmin\Repositories;

class Administrator extends \Dcat\Admin\Http\Repositories\Administrator
{
    public $eloquentClass = \Modules\DcatAdmin\Models\Administrator::class;
}
