<?php

namespace Modules\Application\DcatAdmin\Controllers;

use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Modules\Application\Models\ApplicationDict;
use Modules\Application\Services\DictService;

/**
 * 字典管理后台控制器
 *
 * 提供Dcat Admin后台字典管理界面
 */
class DictController extends AdminController
{
    /**
     * 页面标题
     *
     * @var string
     */
    protected $title = '字典管理';

    /**
     * 列表页
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(ApplicationDict::class, function (Grid $grid) {
            $grid->column('id', 'ID')->sortable();
            $grid->column('dict_type', '字典类型');
            $grid->column('dict_label', '字典标签');
            $grid->column('dict_value', '字典值');
            $grid->column('dict_sort', '排序')->sortable();
            $grid->column('status', '状态')->switch();
            $grid->column('is_client', '客户端可用')->switch();
            $grid->column('created_at', '创建时间');
            $grid->column('updated_at', '更新时间');

            // 筛选器
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('dict_type', '字典类型');
                $filter->like('dict_label', '字典标签');
                $filter->equal('status', '状态')->select([
                    0 => '禁用',
                    1 => '启用',
                ]);
                $filter->equal('is_client', '客户端可用')->select([0 => '否', 1 => '是']);
            });

            // 快速创建
            $grid->quickCreate(function (Grid\Tools\QuickCreate $create) {
                $create->text('dict_type', '字典类型');
                $create->text('dict_label', '字典标签');
                $create->text('dict_value', '字典值');
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
        return Form::make(ApplicationDict::class, function (Form $form) {
            $form->display('id', 'ID');
            $form->text('dict_type', '字典类型')->required();
            $form->text('dict_label', '字典标签')->required();
            $form->text('dict_value', '字典值')->required()
                ->rules('unique:application_dict,dict_value,{{id}},id,dict_type,' . request('dict_type'));
            $form->number('dict_sort', '排序')->default(0);
            $form->switch('status', '状态')->default(1);
            $form->switch('is_client', '客户端可用')->default(0);
            $form->textarea('remark', '备注');
            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');

            // 保存后清除字典缓存
            $form->saved(function () {
                DictService::clearCache();
            });
        });
    }

    /**
     * 详情页
     *
     * @return mixed
     */
    protected function detail(): mixed
    {
        return Form::make(ApplicationDict::class, function (Form $form) {
            $form->display('id', 'ID');
            $form->display('dict_type', '字典类型');
            $form->display('dict_label', '字典标签');
            $form->display('dict_value', '字典值');
            $form->display('dict_sort', '排序');
            $form->display('status', '状态');
            $form->display('is_client', '客户端可用');
            $form->display('remark', '备注');
            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');
        });
    }
}
