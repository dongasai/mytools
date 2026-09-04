<?php

namespace Modules\DcatAdmin\DcatAdmin\Metrics\User;

use Illuminate\Http\Request;
use Modules\DcatAdmin\DcatAdmin\Metrics\Examples\Number;
use Modules\User\Services\Online as OnlineService;

class Online extends Number
{
    protected $title = '在线用户';

    public function handle(Request $request)
    {
        $this->withContent(OnlineService::count());
    }
}
