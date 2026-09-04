<?php

namespace Modules\FeatureSms\DcatAdmin\Controllers;

use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Modules\DcatAdmin\DcatAdmin\AdminController;
use Modules\FeatureSms\DcatAdmin\Repositories\SmsDbGatewayRepository;

/**
 * db驱动-短信记录管理
 */
class SmsGatewayController extends AdminController
{
    protected $title = 'db驱动-短信记录';

    protected $description = '查看通过db驱动发送的短信记录，用于开发测试环境';

    /**
     * Make a grid builder.
     */
    protected function grid(): Grid
    {
        return Grid::make(new SmsDbGatewayRepository, function (Grid $grid) {
            $grid->column('id', 'ID');
            $grid->column('name', '网关名称');
            $grid->column('driver', '驱动类型');
            $grid->column('priority', '优先级');
            $grid->column('status', '状态')->using([
                1 => '启用',
                0 => '禁用',
            ])->label([
                1 => 'success',
                0 => 'danger',
            ]);
            $grid->column('created_at', '创建时间');
            $grid->column('updated_at', '更新时间');

            $grid->enableQuickCreateButton();
            $grid->enableBatchActions();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id', 'ID');
                $filter->like('name', '网关名称');
                $filter->equal('driver', '驱动类型');
                $filter->equal('status', '状态')->select([
                    1 => '启用',
                    0 => '禁用',
                ]);
            });
        });
    }

    /**
     * Make a show builder.
     *
     * @param  mixed  $id
     */
    protected function detail($id): Show
    {
        return Show::make($id, new SmsDbGatewayRepository, function (Show $show) {
            $show->field('id', 'ID');
            $show->field('name', '网关名称');
            $show->field('driver', '驱动类型');
            $show->field('priority', '优先级');
            $show->field('status', '状态')->using([
                1 => '启用',
                0 => '禁用',
            ]);
            $show->field('config', '配置内容')->json();
            $show->field('created_at', '创建时间');
            $show->field('updated_at', '更新时间');
        });
    }

    /**
     * Make a form builder.
     */
    protected function form(): Form
    {
        return Form::make(new SmsDbGatewayRepository, function (Form $form) {
            $form->display('id', 'ID');
            $form->text('name', '网关名称')->required();
            $form->text('driver', '驱动类型')->required();
            $form->number('priority', '优先级')->default(1)->min(1);
            $form->switch('status', '状态')->default(1);
            $form->textarea('config', '配置内容')->help('JSON格式的配置内容');

            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');
        });
    }
}
