<?php

namespace Modules\Application\DcatAdmin\Controllers;

use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Modules\Application\Models\Feature;

/**
 * 功能管理后台控制器
 *
 * 提供Dcat Admin后台功能管理界面
 */
class FeatureController extends AdminController
{
    /**
     * 页面标题
     *
     * @var string
     */
    protected $title = '功能管理';

    /**
     * 列表页
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(Feature::class, function (Grid $grid) {
            $grid->column('id', 'ID')->sortable();
            $grid->column('name', '功能名称');
            $grid->column('key', '功能标识');
            $grid->column('group', '功能分组');
            $grid->column('percentage', '灰度百分比')->display(function ($value) {
                return $value . '%';
            });
            $grid->column('description', '功能描述')->limit(50);
            $grid->column('is_enabled', '默认状态')->switch();
            $grid->column('created_at', '创建时间');
            $grid->column('updated_at', '更新时间');

            // 筛选器
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id', 'ID');
                $filter->equal('key', '功能标识');
                $filter->like('name', '功能名称');
                $filter->equal('group', '功能分组')->select([
                    '创作类' => '创作类',
                    '社交类' => '社交类',
                    'AI类' => 'AI类',
                    'VIP特权' => 'VIP特权',
                    '实验性' => '实验性',
                ]);
                $filter->equal('is_enabled', '默认状态')->select([
                    0 => '关闭',
                    1 => '开启',
                ]);
            });

            // 快速创建（仅支持text/select）
            $grid->quickCreate(function (Grid\Tools\QuickCreate $create) {
                $create->text('name', '功能名称');
                $create->text('key', '功能标识');
                $create->select('group', '功能分组')->options([
                    '创作类' => '创作类',
                    '社交类' => '社交类',
                    'AI类' => 'AI类',
                    'VIP特权' => 'VIP特权',
                    '实验性' => '实验性',
                ]);
            });

            // 操作按钮
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->append('<a href="' . route('application.features.whitelist', ['feature_id' => $actions->row->id]) . '" class="btn btn-sm btn-primary">白名单</a>');
                $actions->append('<a href="' . route('application.features.blacklist', ['feature_id' => $actions->row->id]) . '" class="btn btn-sm btn-danger">黑名单</a>');
            });
        });
    }

    /**
     * 表单页
     *
     * @return Form
     */
    protected function form(): Form
    {
        return Form::make(Feature::class, function (Form $form) {
            $form->display('id', 'ID');
            $form->text('name', '功能名称')->required();
            $form->text('key', '功能标识')->required()->rules('unique:features,key,{{id}}');
            $form->select('group', '功能分组')->options([
                '创作类' => '创作类',
                '社交类' => '社交类',
                'AI类' => 'AI类',
                'VIP特权' => 'VIP特权',
                '实验性' => '实验性',
            ]);
            $form->number('percentage', '灰度百分比')
                ->min(0)
                ->max(100)
                ->default(0)
                ->help('0表示不启用灰度百分比,100表示全量开放');
            $form->textarea('description', '功能描述');
            $form->switch('is_enabled', '默认状态');
            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');
        });
    }

    /**
     * 详情页
     *
     * @return mixed
     */
    protected function detail(): mixed
    {
        return Form::make(Feature::class, function (Form $form) {
            $form->display('id', 'ID');
            $form->display('name', '功能名称');
            $form->display('key', '功能标识');
            $form->display('group', '功能分组');
            $form->display('percentage', '灰度百分比');
            $form->display('description', '功能描述');
            $form->display('is_enabled', '默认状态');
            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');
        });
    }
}