<?php

namespace Modules\FeatureSms\DcatAdmin\Controllers;

use Dcat\Admin\Grid;
use Modules\DcatAdmin\DcatAdmin\AdminController;
use Modules\DcatAdmin\DcatAdmin\GridHelper;
use Modules\FeatureSms\DcatAdmin\Repositories\SmsDbGatewayRepository;

/**
 * db驱动-短信记录(只读)
 */
class MyGatewayController extends AdminController
{
    protected $title = '短信记录查看';

    protected $description = '只读查看db驱动发送的短信记录';
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        $grid = Grid::make(new SmsDbGatewayRepository, function (Grid $grid) {
            $helper = new GridHelper($grid, $this);

            $helper->columnIdDesc();
            $grid->column('content');
            $grid->column('tpl_value');
            $grid->column('universal_number');
            $grid->column('mobile');
            $helper->columnAtd('created_at');

            $grid->disableCreateButton();
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->disableDelete();
                $actions->disableEdit();
                $actions->disableView();
                $actions->disableQuickEdit();
            });
            $grid->disableBatchActions();
            $grid->disableQuickEditButton();
            $grid->disableDeleteButton();
            $grid->disablePagination();
        });

        return $grid;
    }

    public function detail() {}
}
