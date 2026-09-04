<?php

namespace Modules\DcatAdmin\DcatAdmin;

use Modules\DcatAdmin\DcatAdmin\Traits\ResController;
use Modules\DcatAdmin\DcatAdmin\Traits\ReturnRes;

class AdminController extends \Dcat\Admin\Http\Controllers\AdminController
{
    use ResController, ReturnRes;
}
