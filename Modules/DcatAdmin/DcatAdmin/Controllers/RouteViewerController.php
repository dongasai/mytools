<?php

namespace Modules\DcatAdmin\DcatAdmin\Controllers;

use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Modules\DcatAdmin\DcatAdmin\Repositories\RouteName;

/**
 * 路由查看控制器
 */
class RouteViewerController extends AdminController
{
    public function grid()
    {
        /**
         *   "middleware" => array:3 [▶]
         * "uses" => "Dcat\Admin\Http\Controllers\HandleFormController@handle"
         * "controller" => "Dcat\Admin\Http\Controllers\HandleFormController@handle"
         * "as" => "dcat.admin.dcat-api.form"
         * "namespace" => "Dcat\Admin\Http\Controllers"
         * "prefix" => "/admin/dcat-api"
         * "where" => []
         */
        return Grid::make(new RouteName, function (Grid $grid) {
            $grid->quickSearch(['controller', 'as', 'namespace', 'uses']);

            $grid->column('as', '路由名称');
            $grid->column('prefix', '路由前缀');
            $grid->column('namespace', '命名空间');
            $grid->column('controller', '控制器');
            $grid->column('uses', '处理方法');

            //            dump($grid)
            // $grid->disableActions(true);
            // $grid->disableBatchActions(true);
            // $grid->disableToolbar(true);
            $grid->disablePagination();
        });
    }
}
