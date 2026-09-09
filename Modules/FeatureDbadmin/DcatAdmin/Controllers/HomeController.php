<?php

namespace Modules\FeatureDbadmin\DcatAdmin\Controllers;

use Dcat\Admin\Layout\Content;
use Modules\DcatAdmin\DcatAdmin\AdminController;

/**
 * FeatureDbadmin 单页面应用入口控制器
 *
 * 提供 Vue 单页面应用的 HTML 入口
 */
class HomeController extends AdminController
{
    /**
     * 单页面应用入口
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->title('数据库管理员工具')
            ->description('统一的数据库管理平台')
            ->body(view('featuredbadmin::vue.app'));
    }
}
