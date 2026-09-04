<?php

namespace Modules\Application\DcatAdmin\Controllers;

use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Illuminate\Http\Request;
use Modules\Application\Enums\VIEW_TYPE;
use Modules\DcatAdmin\DcatAdmin\AdminController;
use Modules\DcatAdmin\DcatAdmin\FilterHelper;
use Modules\DcatAdmin\DcatAdmin\Grid\Views\GridHeader;
use Modules\DcatAdmin\DcatAdmin\GridHelper;
use Modules\DcatAdmin\Models\AdminGridView;

class AdminGridViewController extends AdminController
{
    protected $title = '列表视图';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new AdminGridView, function (Grid $grid) {
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
                //                $filter->equal('id');
                $filter->equal('id');
                $filter->equal('admin_id');

            });

        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new AdminGridView, function (Form $form) {
            //            dump(redirect()->getIntendedUrl());
            $form->display('id');
            // $form->display('p1');
            // dd($form);
            $form->textarea('p1', '参数(不能改)')
                ->help('不要修改这个!')
                ->disable()
                ->placeholder('不要修改这个!');

            $form->text('title', '标题')->required();
            $form->radio('type1', '类型')->options(VIEW_TYPE::getValueDescription());
            $form->saving(function (Form $form) {
                // 删除
                $form->deleteInput('p1');
            });

            $form->saved(function (Form $form) {

                return $form->response()->success('保存成功')->redirectToIntended('');
            });

        });
    }

    /**
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
        //        unset($get['_viewid']);
        if ($id) {
            $model = AdminGridView::query()->find($id);
            $router_name = $model->router_name;
            admin_success('视图更新成功');
        } else {

            $model = new AdminGridView;
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
