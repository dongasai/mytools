<?php

namespace Modules\Application\DcatAdmin\Controllers;

use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Modules\DcatAdmin\DcatAdmin\FilterHelper;
use Modules\DcatAdmin\DcatAdmin\FormHelper;
use Modules\DcatAdmin\DcatAdmin\GridHelper;
use Modules\Application\DcatAdmin\Repositories\AdminActionLog;

/**
 * 优惠券
 */
class ActionLogController extends \Dcat\Admin\Http\Controllers\AdminController
{
    protected $title = '操作日志';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new AdminActionLog, function (Grid $grid) {
            $grid->model()->orderByDesc('id')->with('admin');
            $helper = new GridHelper($grid, $this);
            $grid->column('id');

            $helper->columnAdminId('admin_id');
            $grid->column('admin.username');

            $grid->column('type1');

            $grid->column('object_class');
            $grid->column('before');

            $grid->column('after');

            $grid->column('created_at')->display(function ($value) {
                return $value ? date('Y-m-d H:i:s', strtotime($value)) : '-';
            });

            //            $grid->dia
            $grid->filter(function (Grid\Filter $filter) {

                $helper = new FilterHelper($filter, $this);

            });
            $grid->disableCreateButton();
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->disableDelete();
                $actions->disableEdit();
                $actions->disableQuickEdit();
            });
            $grid->disableBatchActions();
            $grid->disableQuickEditButton();
            $grid->disableDeleteButton();
        });
    }

    public function detail() {}

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form() {}

    /**
     * 编辑表单
     *
     * @return void
     */
    private function form_edit(Form &$form, FormHelper &$helper) {}
}
