<?php

namespace Modules\DcatAdmin\DcatAdmin\Controllers;

use Dcat\Admin\Layout\Content;
use Modules\DcatAdmin\DcatAdmin\AdminController;

/**
 * Vue 仪表盘控制器
 */
class VueDashboardController extends AdminController
{
    /**
     * Vue 仪表盘
     */
    public function index(Content $content)
    {
        return $this->vueview($content, 'module_dcatadmin::vue.dashboard', []);
    }

    /**
     * Element Plus 组件演示
     */
    public function elementsDemo(Content $content)
    {
        return $this->vueview($content, 'module_dcatadmin::vue.elements-demo', []);
    }

    /**
     * 基础组件演示
     */
    public function basicDemo(Content $content)
    {
        return $this->vueview($content, 'module_dcatadmin::vue.basic-demo', []);
    }

    /**
     * 表单组件演示
     */
    public function formDemo(Content $content)
    {
        return $this->vueview($content, 'module_dcatadmin::vue.form-demo', []);
    }

    /**
     * 数据展示演示
     */
    public function dataDemo(Content $content)
    {
        return $this->vueview($content, 'module_dcatadmin::vue.data-demo', []);
    }
}