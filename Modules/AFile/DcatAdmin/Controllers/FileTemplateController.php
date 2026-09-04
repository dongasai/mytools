<?php

namespace Modules\AFile\DcatAdmin\Controllers;

use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Modules\AFile\DcatAdmin\Repositories\FileTemplateRepository;
use Modules\DcatAdmin\DcatAdmin\AdminController;

/**
 * 文件模板管理控制器
 */
class FileTemplateController extends AdminController
{
    /**
     * 页面标题
     *
     * @var string
     */
    protected $title = '文件模板管理';

    /**
     * 列表页面
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new FileTemplateRepository, function (Grid $grid) {
            $grid->column('id', 'ID')->sortable();
            $grid->column('unid', '标识');
            $grid->column('title', '模板标题');
            $grid->column('desc', '描述');
            $grid->column('group', '分组');
            $grid->column('file_id', '关联文件ID');
            $grid->column('status', '状态')->display(function ($s) {
                return $s == 1 ? '有效' : '无效';
            })->label([1 => 'success', 0 => 'danger']);
            $grid->column('created_at', '创建时间')->display(function ($value) {
                return $value ? date('Y-m-d H:i:s', strtotime($value)) : '-';
            });
            $grid->column('updated_at', '更新时间')->display(function ($value) {
                return $value ? date('Y-m-d H:i:s', strtotime($value)) : '-';
            });

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id', 'ID');
                $filter->like('unid', '标识');
                $filter->like('title', '模板标题');
                $filter->like('group', '分组');
                $filter->equal('status', '状态')->select([0 => '无效', 1 => '有效']);
            });
        });
    }

    /**
     * 详情页面
     *
     * @param  mixed  $id
     * @return Show
     */
    protected function detail($id)
    {
        return Show::make($id, new FileTemplateRepository, function (Show $show) {
            $show->field('id', 'ID');
            $show->field('unid', '标识');
            $show->field('title', '模板标题');
            $show->field('desc', '描述');
            $show->field('group', '分组');
            $show->field('file_id', '关联文件ID');
            $show->field('status', '状态')->as(function ($s) {
                return $s == 1 ? '有效' : '无效';
            });
            $show->field('created_at', '创建时间');
            $show->field('updated_at', '更新时间');
        });
    }

    /**
     * 表单页面
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new FileTemplateRepository, function (Form $form) {
            $form->display('id');

            $form->text('unid', '标识')->required();
            $form->text('title', '模板标题')->required();
            $form->text('desc', '描述');
            $form->text('group', '分组');

            $form->select('file_id', '关联文件')->options(function () {
                return \Modules\AFile\Models\FileImg::query()->pluck('o_name', 'id')->toArray();
            });

            $form->radio('status', '状态')->options([0 => '无效', 1 => '有效'])->default(1);

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
