<?php

namespace Modules\DcatAdmin\DcatAdmin\Controllers;

use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Modules\DcatAdmin\DcatAdmin\Repositories\TraceRepository;

/**
 * 支付订单
 */
class TraceController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        return Grid::make(new TraceRepository, function (Grid $grid) {

            $grid->column('id');
            $grid->column('id')->expand(function (\Dcat\Admin\Grid\Displayers\Expand $expand) {
                //                dd($expand);
                return view('module_dcatadmin::dev.trace', [
                    'unid' => $this->id,
                ]);
            });

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('unid');
                $filter->expand();
                $filter->panel();
            });

            $grid->disableCreateButton();
            $grid->disableActions();
            $grid->disableToolbar(true);
            $grid->disablePagination();

        });
    }
}
