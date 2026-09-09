<?php

namespace Modules\FeatureDbadmin\DcatAdmin\Controllers;

use Dcat\Admin\Layout\Content;
use Modules\DcatAdmin\DcatAdmin\AdminController;

/**
 * FeatureDbadmin 单页面应用入口控制器
 *
 * 控制器只返回视图，所有渲染逻辑交给 vue-app 布局
 */
class HomeController extends AdminController
{
    /**
     * 单页面应用入口
     *
     * @return \Illuminate\View\View
     */
    public function home(Content $content)
    {
       return  $this->vueview($content,'featuredbadmin::vue.app',[]);
    }
}
