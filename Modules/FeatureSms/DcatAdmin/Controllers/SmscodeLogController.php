<?php

namespace Modules\FeatureSms\DcatAdmin\Controllers;

use Dcat\Admin\Grid;
use Modules\DcatAdmin\DcatAdmin\FilterHelper;
use Modules\FeatureSms\DcatAdmin\Repositories\SmsCodeRepository;

/**
 * 短信验证码日志
 */
class SmscodeLogController extends \Modules\DcatAdmin\DcatAdmin\AdminController
{
    protected $title = '短信验证码日志';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new SmsCodeRepository, function (Grid $grid) {
            $grid->model()->orderByDesc('id');
            $helper = new \Modules\DcatAdmin\DcatAdmin\GridHelper($grid, $this);
            $grid->column('id');

            $grid->column('mobile', '手机号码');
            $grid->column('token');

            $grid->column('type', '类型');
            $grid->column('code_value', '验证码');

            $grid->column('created_at');

            //            $grid->dia
            $grid->filter(function (Grid\Filter $filter) {

                $helper = new FilterHelper($filter, $this);
                $filter->equal('mobile', '手机');
            });
            //            $grid->disableCreateButton();
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->disableDelete();
                $actions->disableEdit();
                $actions->disableQuickEdit();
            });
            $grid->disableBatchActions();
            $grid->disableQuickEditButton();
            $grid->disableDeleteButton();
            $grid->disableViewButton();
        });
    }
}
