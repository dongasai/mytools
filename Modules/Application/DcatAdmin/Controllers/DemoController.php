<?php

namespace Modules\Application\DcatAdmin\Controllers;

use Dcat\Admin\Layout\Content;

/**
 * Demo控制器
 */
class DemoController extends \Dcat\Admin\Http\Controllers\AdminController
{
    /**
     * Debug方法 - 输出配置信息
     *
     */
    public function debug( )
    {
        dump(config());

       
    }
}