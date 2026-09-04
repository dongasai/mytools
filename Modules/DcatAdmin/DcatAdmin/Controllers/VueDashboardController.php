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
        // standalone 模式直接返回视图（无 Dcat Admin 包裹）
        if (request()->get('standalone')) {
            return view('module_dcatadmin::vue.dashboard');
        }

        // 正常模式返回带 Dcat Admin 布局的响应
        return $content
            ->title('Vue 仪表盘')
            ->body(view('module_dcatadmin::vue.dashboard'));
    }

    /**
     * Element Plus 组件演示
     */
    public function elementsDemo(Content $content)
    {
        // standalone 模式直接返回视图（无 Dcat Admin 包裹）
        if (request()->get('standalone')) {
            return view('module_dcatadmin::vue.elements-demo');
        }

        // 正常模式返回带 Dcat Admin 布局的响应
        return $content
            ->title('Element Plus 组件演示')
            ->body(view('module_dcatadmin::vue.elements-demo'));
    }

    /**
     * 基础组件演示
     */
    public function basicDemo(Content $content)
    {
        // standalone 模式直接返回视图（无 Dcat Admin 包裹）
        if (request()->get('standalone')) {
            return view('module_dcatadmin::vue.basic-demo');
        }

        // 正常模式返回带 Dcat Admin 布局的响应
        return $content
            ->title('基础组件演示')
            ->body(view('module_dcatadmin::vue.basic-demo'));
    }

    /**
     * 表单组件演示
     */
    public function formDemo(Content $content)
    {
        // standalone 模式直接返回视图（无 Dcat Admin 包裹）
        if (request()->get('standalone')) {
            return view('module_dcatadmin::vue.form-demo');
        }

        // 正常模式返回带 Dcat Admin 布局的响应
        return $content
            ->title('表单组件演示')
            ->body(view('module_dcatadmin::vue.form-demo'));
    }

    /**
     * 数据展示演示
     */
    public function dataDemo(Content $content)
    {
        // standalone 模式直接返回视图（无 Dcat Admin 包裹）
        if (request()->get('standalone')) {
            return view('module_dcatadmin::vue.data-demo');
        }

        // 正常模式返回带 Dcat Admin 布局的响应
        return $content
            ->title('数据展示演示')
            ->body(view('module_dcatadmin::vue.data-demo'));
    }
}