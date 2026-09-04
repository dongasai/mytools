<?php

namespace Modules\DcatAdmin\DcatAdmin\Controllers;

use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;

/**
 * Session管理
 */
class SessionController extends AdminController
{
    protected $title = 'Session管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new \Modules\DcatAdmin\DcatAdmin\Repositories\Session, function (Grid $grid) {
            $grid->model()->orderBy('id', 'desc');
            $grid->column('id', 'ID');
            $grid->column('content', '内容');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->expand();
                $filter->panel();
            });

            $grid->disableCreateButton();
            $grid->disableActions();
            $grid->disablePagination();
        });
    }
}
