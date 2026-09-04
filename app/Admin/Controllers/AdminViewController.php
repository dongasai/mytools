<?php

namespace App\Admin\Controllers;

use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Modules\DcatAdmin\Models\AdminGridView;
use Modules\Application\Enums\VIEW_TYPE;
use Modules\DcatAdmin\DcatAdmin\Grid\Views\GridHeader;
use Modules\DcatAdmin\DcatAdmin\GridHelper;
use Modules\DcatAdmin\DcatAdmin\FilterHelper;

/**
 * 后台视图管理控制器
 *
 * 用于管理 Grid 视图的保存、编辑和删除
 */
class AdminViewController extends AdminController
{
    protected $title = '列表视图';

    /**
     * 视图列表 Grid
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new AdminGridView(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $helper = new GridHelper($grid, $this);
            $helper->columnAdminId();

            $helper->columnModelCats('type1');
            $grid->column('title');
            $grid->column('p1');

            $helper->columnAtd('created_at');
            $helper->columnAtd('updated_at');

            GridHeader::gridHeader($grid);

            $grid->filter(function (Grid\Filter $filter) {
                $help = new FilterHelper($filter, $this);
                $filter->equal('id');
                $filter->equal('admin_id');
            });
        });
    }

    /**
     * 视图编辑表单
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new AdminGridView(), function (Form $form) {
            $form->display('id');

            $form->textarea('p1', '参数(不能改)')
                ->help('不要修改这个!')
                ->disable()
                ->placeholder('不要修改这个!');

            $form->text('title', '标题')->required();
            $form->radio('type1', '类型')->options(VIEW_TYPE::getValueDescription());

            $form->saving(function (Form $form) {
                // 删除 p1 字段的修改
                $form->deleteInput('p1');
            });

            $form->saved(function (Form $form) {
                return $form->response()->success('保存成功')->redirectToIntended('');
            });
        });
    }

    /**
     * 添加或更新视图
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getadd(Request $request)
    {
        $get = $request->query();
        $router_name = $get['_router_name'] ?? '';
        unset($get['_router_name']);
        unset($get['_pjax']);
        unset($get['pjax']);

        $id = $get['_viewid'] ?? 0;

        if ($id) {
            // 更新现有视图
            $model = AdminGridView::query()->find($id);
            $router_name = $model->router_name;
            admin_success('视图更新成功');
        } else {
            // 创建新视图
            $model = new AdminGridView();
            $model->admin_id = Admin::user()->getKey();
            $model->type1 = VIEW_TYPE::PUBLIC;
            $model->router_name = $router_name;
            $model->title = '自定义视图';
            admin_success('视图创建成功');
        }

        $model->p1 = $get;
        $model->save();

        return redirect()->route($router_name, $get);
    }
}