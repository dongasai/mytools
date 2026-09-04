<?php

namespace Modules\DcatAdmin\DcatAdmin\Controllers;

use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use DLaravel\Model\RequestLog;
use Modules\DcatAdmin\DcatAdmin\GridHelper;
use Modules\Application\Admin\Repositories\RequireLog;

/**
 * 任务相关（计划任务/队列）
 */
class JobJobrunController extends AdminController
{
    protected $title = '任务-Jobrun';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        return Grid::make(new RequireLog, function (Grid $grid) {

            $helper = new GridHelper($grid, $this);
            $grid->model()->orderBy('id', 'desc');
            $grid->column('id');
            $grid->column('unid')->expand(function (\Dcat\Admin\Grid\Displayers\Expand $value) {
                return view('module_dcatadmin::dev.trace', [
                    'unid' => $this->unid,
                ]);
            });

            $grid->column('path');
            $grid->column('token')->copyable();

            $grid->column('user_id');

            $grid->column('router');
            $grid->column('data')->expand(function (\Dcat\Admin\Grid\Displayers\Expand $value) {

                return RequireLogData::make();
            });
            $helper->columnAtd('created_at');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('unid');
                $filter->equal('user_id');
                $filter->equal('token');

                $filter->equal('path')->select(RequestLog::selectPath());
                $filter->between('created_at')->datetime();
                $filter->expand();
                $filter->panel();
            });

            $grid->disableCreateButton();
            $grid->disableActions();
            $grid->disableToolbar(true);

        });
    }
}
